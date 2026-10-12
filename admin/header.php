<?php
$scriptname=$_SERVER['PHP_SELF'];

$pos=strpos($_SERVER['HTTP_HOST'] , "." );
if ($pos === false) {
	$ssdomville="www";
} elseif(substr($_SERVER['HTTP_HOST'] , $pos )==".fr") {
	$ssdomville="www";
} else {
	$ssdomville=substr($_SERVER['HTTP_HOST'] , 0 , $pos );
}

if ($ssdomville=="www") {
	$cloak="";
} else {
	$cloak="sur $ssdomville";
}

$pagetitle="&#9733; Panneau d'administration $host &#9733; $cloak";
$pagedescription="&#9733; Panneau d'administration  $host &#9733; $cloak";
$pagekeywords="Panneau d'administration $host $cloak";


if (strpos($_SERVER['HTTP_HOST'],"vendre-en-ligne.fr")===FALSE){
	$domaine="boutiques en ligne";
	$flash="boutique";
}else{
	$domaine="vendre en ligne";
	$flash="vendre";
}

if (isset($visite) and $visite==1) {
	$flash.="_stat";
}
$bodyonload = "";
//$bodyonload = "onLoad=\"myInit();\"";
?>
<html lang="fr">
    <head>
		<title><?= $pagetitle ?></title>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
		<LINK href="http://localhost/mec/admin/vendre-en-ligne.css" type="text/css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
        <link href="http://localhost/mec/admin/css/styles.css" rel="stylesheet" />
        <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
		<?php //echo $js ?>
        <script language="javascript" src="http://localhost/mec/admin/js/scripts.js"></script>

        <script language="javascript" src="http://localhost/mec/javascripts/ajax.js"></script>
		<script language="javascript" src="http://localhost/mec/admin/tel/epigy.js?v=1"></script>
		<!--<script src="https://code.jquery.com/jquery-3.3.1.min.js" integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous"></script>-->
		<script src="//code.jquery.com/jquery-2.2.0.min.js"></script>
		<script src="//code.jquery.com/ui/1.11.4/jquery-ui.min.js"></script>
		<script type="text/javascript" src="http://localhost/mec/admin/javascript/jquery.js"></script>
		<script language="javascript" src="http://localhost/mec/admin/calendrier.js"></script>
		<script language="javascript" src="http://localhost/mec/admin/taskmanager/scripts/task_popup.js"></script>
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
        <script language="javascript">
            function checkMail(mail){
                var reg = new RegExp('^[a-z0-9]+([_|\.|-]{1}[a-z0-9]+)*@[a-z0-9]+([_|\.|-]{1}[a-z0-9]+)*[\.]{1}[a-z]{2,6}$', 'i');
                return(reg.test(mail));
            }
            function showlivr(){
                if(!document.getElementById('livrid').checked){
                    document.getElementById('livrtbl').style.display='none';
                    document.getElementById('mentions').style.display='none';
                    if(document.documentElement.clientWidth>=640)
                        document.getElementById('img').style.display='';
                }else{
                    document.getElementById('livrtbl').style.display='block';
                    document.getElementById('mentions').style.display='block';
                    /*if(document.documentElement.clientWidth>=640)
                    document.getElementById('img').style.display='none';*/
                }
            }

            function sendit(){
                err='';
                /*var cgv = document.getElementById('devis_immediat_cgv').getElementsByTagName('input')[0].checked;
                if(!cgv) err+='{$template->translate("/client/erreur_cgv","F")}.';*/
                if(document.getElementById('nom').value=='') err+=' {$template->translate("/client/erreur_nom","F")}.';
                if(document.getElementById('prenom').value=='') err+=' {$template->translate("/client/erreur_prenom","F")}.';
                if(document.getElementById('mail').value=='') err+=' {$template->translate("/client/indiquer_mail_valide","F")}.';
                else if(!checkMail(document.getElementById('mail').value)) err+=' {$template->translate("/client/indiquer_mail_valide","F")}.';
                if(document.getElementById('telephone').value+document.getElementById('portable').value=='') err+=' {$template->translate("/client/erreur_telephone","F")}.';
                if(document.getElementById('cp').value=='') err+=' {$template->translate("/client/erreur_codepostal","F")}.';
                if(document.getElementById('pays').value=='') err+=' {$template->translate("/client/erreur_pays","F")}.';
                if(document.getElementById('livrid').checked && document.getElementById('nom_livr').value=='') err+=' {$template->translate("/client/erreur_nom","F")}.';
                if(document.getElementById('livrid').checked && document.getElementById('prenom_livr').value=='') err+=' {$template->translate("/client/erreur_prenom","F")}.';
                if(document.getElementById('livrid').checked && document.getElementById('adresse1_livr').value=='') err+=' {$template->translate("/client/erreur_adresse","F")}.';
                if(document.getElementById('livrid').checked && document.getElementById('ville_livr').value=='') err+=' {$template->translate("/client/erreur_ville","F")}.';
                if(document.getElementById('livrid').checked && document.getElementById('payslivr').value=='') err+=' {$template->translate("/client/erreur_pays","F")}.';
                if(document.getElementById('livrid').checked && document.getElementById('cp_livr').value=='') err+=' {$template->translate("/client/erreur_codepostal","F")}.';
                /*if(!document.getElementById('conseils0').checked && !document.getElementById('conseils1').checked) err+=' {$template->translate("/client/erreur_conseils","F")}';*/
                if(err!=''){
                    alert(err);
                    return;
                }

                document.getElementById('sendbtn').style.display='none';
                document.getElementById('waiticon').style.display='block';
                params='societe='+document.getElementById('societe').value;
                params+='&nom='+document.getElementById('nom').value;
                params+='&civ='+document.getElementById('civ').value;
                params+='&prenom='+document.getElementById('prenom').value;
                params+='&adresse1='+document.getElementById('adresse1').value;
                params+='&cp='+document.getElementById('cp').value;
                params+='&ville='+document.getElementById('ville').value;
                params+='&mail='+document.getElementById('mail').value;
                params+='&telephone='+document.getElementById('telephone').value;
                params+='&portable='+document.getElementById('portable').value;
                params+='&pays='+document.getElementById('pays').value;
                params+='&payslivr='+document.getElementById('payslivr').value;
                params+='&livrid='+document.getElementById('livrid').checked;
                params+='&devisartid='+document.getElementById('devisartid').value;
                params+='&societe_livr='+document.getElementById('societe_livr').value;
                params+='&nom_livr='+document.getElementById('nom_livr').value;
                params+='&prenom_livr='+document.getElementById('prenom_livr').value;
                params+='&adresse1_livr='+document.getElementById('adresse1_livr').value;
                params+='&cp_livr='+document.getElementById('cp_livr').value;
                params+='&ville_livr='+document.getElementById('ville_livr').value;
                params+='&telephone_livr='+document.getElementById('telephone_livr').value;

                ajaxrequest('devisrapide_ajax.php?'+params,cb);

            }

            function cb(httprequest){
                document.getElementById('main').innerHTML=httprequest.responseText;
            }
        </script>
		<LINK href="http://localhost/mec/admin/css/style2.css" type="text/css" rel="stylesheet">
		<LINK href="http://localhost/mec/admin/taskmanager/css/smoothness/jquery-ui-1.10.3.custom.min.css" type="text/css" rel="stylesheet">
		<LINK href="https://maxcdn.bootstrapcdn.com/font-awesome/4.6.3/css/font-awesome.min.css" rel="stylesheet">
		<script src="http://localhost/mec/admin/scripts/jquery.ui.datepicker-fr.php"></script>
		<META NAME="TITLE" CONTENT="<?= $pagetitle?>">
		<META NAME="AUTHOR" CONTENT="Ma Cave à Vin">
		<META NAME="SUBJECT" CONTENT="Vente de caves à vin pour particuliers et professionnels">
		<META NAME="RATING" CONTENT="Vente en ligne">
		<META NAME="DESCRIPTION" CONTENT="<?= $pagedescription?>">
		<META NAME="ABSTRACT" CONTENT="Vente de caves à vin pour particuliers et professionnels">
		<META NAME="KEYWORDS" CONTENT="<?= $pagekeywords?>">
		<META NAME="LANGUAGE" CONTENT="FR">
		<META NAME="COPYRIGHT" CONTENT="Ma Cave à Vin">
		<META NAME="ROBOTS" CONTENT="All">
		<META HTTP-EQUIV="PRAGMA" CONTENT="NO-CACHE">
		<link rel="shortcut icon" type="image/x-icon" href="/favicon.ico">
		<link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css" rel="stylesheet" type="text/css">
    </head>
	<BODY class="sb-nav-fixed" BGCOLOR="#FFFFFF" LEFTMARGIN="0" TOPMARGIN="0" MARGINWIDTH="0" MARGINHEIGHT="0" bottommargin="0" rightmargin="0" <?php echo "$bodyonload"; ?>>
		<?=$set_cookies; ?>
		<DIV class="popperlink" id="topdecklink">
		<SCRIPT language="JavaScript">
		var langues=new Array('<?php print implode("','",$langues); ?>');
		domok = document.getElementById;
		if (domok)
		 {
		 skn = document.getElementById("topdecklink").style;
		 /*if(navigator.appName.substring(0,3) == "Net")
		  document.captureEvents(Event.MOUSEMOVE);
		 document.onmousemove = get_mouse;*/
		 }
		function poplink(msg)
		{
		var content ="<TABLE BORDER=0 CELLPADDING=0 CELLSPACING=0 BGCOLOR=#000000><TR><TD><TABLE WIDTH=100% BORDER=0 CELLPADDING=2 CELLSPACING=1><TR><TD BGCOLOR=#FFFFFF><FONT COLOR=#000000><CENTER>"+msg+"</CENTER></TD></TR></TABLE></TD></TR></TABLE>";
		if (domok)
		  {
			document.getElementById("topdecklink").innerHTML = content;
			skn.visibility = "visible";
			}
		}
		function get_mouse(e)
		 {
		 var x = (navigator.appName.substring(0,3) == "Net") ? e.pageX : event.x+document.body.scrollLeft;
		 var y = (navigator.appName.substring(0,3) == "Net") ? e.pageY : event.y+document.body.scrollTop;
		 skn.left = x - 60;
		 skn.top = y+20;
		 }
		function killlink()
		 {
		 if (domok)
			skn.visibility = "hidden"; 
		 }

		function settranslate(name,value,display){
			document.getElementsByName(name)[0].value=value;
			document.getElementById("translate_"+name).innerHTML=display;
			document.getElementById('list_'+name).style.display='none';
			setmodif();
		} 
		var translatefield;
		function showtranslate(name,path){
			if(document.getElementById('create_translate')) return;
			var translatelist=document.getElementById('list_'+name);
			var disp=translatelist.style.display;
			hidetranslate();
			translatefield=name;
			translatelist.style.display=(disp=="block"?"none":"block");
			var translateselect=translatelist.previousElementSibling;
			var rect = translateselect.getBoundingClientRect();
			translatelist.style.left=rect.left+"px";
			//translatelist.style.top=(rect.top+translateselect.clientHeight)+"px";
			if(disp!="block") ajaxrequest("traduction_ajax.php?path="+path+"&name="+name,showtranslate_cb);
		}
		function showtranslate_cb(httprequest){
			document.getElementById('list_'+translatefield).innerHTML=httprequest.responseText;
		}
		function hidetranslate(){
			var ul=document.getElementsByTagName('ul');
			for(i=0;i<ul.length;i++)
			if(ul[i].id.substr(0,5)=="list_") ul[i].style.display="none";

		}
		function checktranslate(name,chk){
			var exvalues=document.getElementsByName(name)[0].value.split(",");
			if(document.getElementsByName(name)[0].value=="") exvalues=new Array();
			
			if(chk.checked)
			  exvalues.push(chk.value);
			else{
			  var exvalues2=new Array();
			  for(i=0;i<exvalues.length;i++){
			  if(exvalues[i]!=chk.value) exvalues2.push(exvalues[i]);
			  }
			  exvalues=exvalues2;
			}
			document.getElementsByName(name)[0].value=exvalues.join(",");
		}
		function newtranslation(name,path){
			hide_newtranslation();
			var div=document.createElement('div');
			div.id="create_translate";
			div.innerHTML="<p>Ajouter une traduction</p>";
			for(i=0;i<langues.length;i++) div.innerHTML+=langues[i]+" <input type=\"text\" id=\"tr_"+langues[i]+"\" size=\"40\"><br>";
			div.innerHTML+="<span id=\"create_translate_error\"></span>";
			div.innerHTML+="<p><input type=\"button\" value=\"Annuler\" onclick=\"hide_newtranslation()\"> <input type=\"button\" value=\"OK\" onclick=\"create_translation('"+path+"')\"></p>";
			document.getElementById('translate_'+name).appendChild(div);
		}

		function hide_newtranslation(){
			var div=document.getElementById('create_translate');
			if(div) div.parentNode.removeChild(div);
		}

		function create_translation(path){
			var post=new Array();
			post['create']=translatefield;
			post['path']=path;
			for(i=0;i<langues.length;i++) post['tr_'+langues[i]]=document.getElementById('tr_'+langues[i]).value;
			ajaxrequest("traduction_ajax.php",create_translation_cb,post);
		}

		function create_translation_cb(httprequest){
			var xml=httprequest.responseXML.documentElement;
			var error=xml.getElementsByTagName('error')[0].firstChild.nodeValue;
			if(error!="OK"){
			  document.getElementById('create_translate_error').innerHTML=error;
			}else{
			  var name=xml.getElementsByTagName('name')[0].firstChild.nodeValue;
			  var path=xml.getElementsByTagName('path')[0].firstChild.nodeValue;
			  var FR=xml.getElementsByTagName('FR')[0].firstChild.nodeValue;
			  hide_newtranslation();
			  settranslate(name,path,FR);
			}
		}

		function checkAll(source,name) {
			checkboxes = document.getElementsByName(name);
			for(var i=0, n=checkboxes.length;i<n;i++) {
			  checkboxes[i].checked = source.checked;
			}
		}

		function round(value,decimals) {
			if(decimals==undefined) decimals=0;
			var coeff=Math.pow(10,decimals);
			return Math.round(coeff*value)/coeff;
		}

		function showDate(){
			if(document.getElementsByName('task_date')[0].value == 3)
				document.getElementById('task_cal').style.display = "";
			else
				document.getElementById('task_cal').style.display = "none";
		}

		function add_inter(value){
			if(document.getElementById("inter_"+value)) return;
			
			if(value != 0)
				document.getElementById('popupinter').innerHTML += "<span id=\"inter_"+value+"\">"+value+"<img src=\"images/supprimer.gif\" onclick=\"rm_inter('"+value+"')\"></span>";

			document.getElementsByName('task_inter')[0].value = 0;

			var input = document.createElement('input');
			input.id="chkinter_"+value;
			input.setAttribute('type','hidden');
			input.setAttribute('name','intervenant[]');
			input.setAttribute('value', value);
			document.getElementById("form_task").appendChild(input);
		}

		function rm_inter(value){
			var span=document.getElementById("inter_"+value);
			document.getElementById('popupinter').removeChild(span);
			var chk=document.getElementById("chkinter_"+value);
			document.getElementById("form_task").removeChild(chk);
		}


		<?php


	print <<<JS
	  //document.body[window.addEventListener ? 'addEventListener' : 'attachEvent'](window.addEventListener ? "load" : "onload", poste_init(), false);
/*	  function poste_init(){
	 
	}*/
JS;


		?>
		</SCRIPT>
		</DIV>
		<style>
		#ui-id-1{z-index:9100;}
		</style>
<?php
$affiche_help = false;
if(is_object($DB_site2) && ((isset($_COOKIE['admin_username']) && !empty($_COOKIE['admin_username'])) || (isset($result) && $result == 1))){
    $sql = "SELECT username, nom_contact";
    $sql .= " FROM parametresgeneraux";
    $sql .= " WHERE actif = 1";
    $sql .= " ORDER BY username ASC";
    $req = $DB_site2->query($sql);
    while($row = $DB_site2->fetch_array($req)){
        $tabResponsable[] = $row;
    }

    $affiche_help = true;

    // gestion de l'affichage des lignes du dropdown tache/bug en decalant la positition de depart
    // Modification de l'affichage est effectuée dans /admin/javascript/jquery.js  
    $position_top = '-62px';
    $aPosition = array(
        1 => '-79px',
        2 => '-95px',
        3 => '-111px',
        4 => '-127px',
        5 => '-143px'
    );

    //Récupération de l'historique des tâches
    $sql = "SELECT id, name, type, DATE_FORMAT(initial_date,'%d/%m/%y %H:%i') as date_creation, UNIX_TIMESTAMP(initial_date) as date_creation_ts";
    $sql .= " FROM suivi_interne";
    $sql .= " WHERE owner = '".$_COOKIE['admin_username']."'";
    $sql .= " ORDER BY id DESC";
    $sql .= " LIMIT 5";
    $req = $DB_site2->query($sql);
    if(isset($_COOKIE['admin_username']) && $DB_site2->num_rows($req) > 0){
        $i = 1;
        while($row = $DB_site2->fetch_array($req)){
            $aHistoriqueTask[$row['id']] = $row;
            $position_top2 = $aPosition[$i]; 
            $i++;
        }
        krsort($aHistoriqueTask);
    }

    //Récupération des RDV du jour - ne doit s'afficher qu'1h avant le rdv
    $liste_rdv = "";
    $today = time();
    $sql = "SELECT p.*, u.prenom, u.nom, u.raisonsociale FROM projet p INNER JOIN utilisateur u ON u.userid = p.userid WHERE p.rappel BETWEEN ".($today - 3600)." AND ".mktime('22','59','59',date("m",$today),date("d",$today),date("Y",$today))." AND p.rappel_commercial ='".$_COOKIE['admin_username']."' AND rappel_type = 'rdv'";
    $req = $DB_site2->query($sql);
    if(isset($_COOKIE['admin_username']) && $DB_site2->num_rows($req) > 0){
        while($row = $DB_site2->fetch_array($req)){
            $liste_rdv .= "<br>".date("d/m H:i",$row['rappel']) . " - <a href='projet.php?projetid=".$row['projetid']."'>" .trim($row['raisonsociale']." ".$row['nom']." ".$row['prenom'])."</a>";
            
        }
    }
}

?>

<div class="main">
    <div class="modal-devis">
        <div class="close-modal">x</div>
        <div class="boutons-devis">
        <a href="" id="devis" class="btn-devis">Devis non détaillé</a>
        <a href="" id="devis-detail" class="btn-devis">Devis détaillé</a>
        <a href="" id="devis-envoi" class="btn-devis">Envoyer le devis</a>
    </div>
    </div>
    <?php 
	/*
	if($affiche_help){ 
	?>
    <div class="help_button2" style="top:<?php echo $position_top2; ?>;">
        <?php 
        if(isset($aHistoriqueTask)){
            foreach($aHistoriqueTask as $l_historique){

                switch($l_historique['type']){
                    case 'bug':
                        $intitule_task = 'SB#';
                        $color         = 'red';
                        break;
                    case 'suivi':
                        $intitule_task = 'SI#';
                        $color         = 'green';
                        break;
                }
                echo "<p><a href=\"".BACK_URL."/task.php?a=showTask&task=".$l_historique['id']."\" style=\"color: ".$color.";\">".$intitule_task.$l_historique['id']." - ".substr($l_historique['name'],0,20)."...</a></p>";
            }
        }
        ?>
    </div>
        <br>
        <?php
		if($liste_rdv !== '' && isset($_COOKIE['admin_username'])){
			?>
			<div class="popup_rdv"><span class="deplie">▲</span><b style="font-size:13px;">RDV du jour</b> <div class="liste_rdv"><?= $liste_rdv ?></div></div>
			<?php
		}
	}
	*/
	?>
    <div class="clear"></div>
    <?php
	if($affiche_help){
		
		?>
    <div id="showbug" style="display: none;">
        <div id="fade" class="close_help_fade"></div> 
        <div class="popup_block">
            <div id="saisie_bug">
                <img src="/admin/images/icon/erreur.png" style="float:right; cursor: pointer;" class="close_help">
                <div style="clear: both;"></div>
                <form method="post" id="form_bug" enctype="multipart/form-data">
                    <div style="margin-top: 25px;" id="step3">
                        <p class="step">D&eacute;crivez le bug que vous avez rencontré, le plus pr&eacute;cis&eacute;ment possible *</p>
                        <input type="text" name="bug_libelle" id="bug_libelle" placeholder="Libellé court...">
                        <textarea name="bug_contenu" id="bug_contenu" placeholder="Description détaillée..."></textarea>
                        <p class="nota_bene">* Essayez d'expliquer la marche &agrave; suivre pour les reproduire, ce qui fera gagner un temps pr&eacute;cieux aux d&eacute;veloppeurs.</p>
                        <br>
                        <div class="cadre"><input type="checkbox" name="urgent" value="1" style="vertical-align: 5px;"> <p class="urgent">Vous &ecirc;tes bloqu&eacute;(e) et ne pouvez avancer correctement dans votre travail, cette demande est donc &agrave; traiter en priorit&eacute; !</p></div>
                        <br>
                        <p>Vous pouvez associer une pi&egrave;ce jointe &agrave; votre requ&ecirc;te ** : </p>
                        <br>
                        <!--<input type="file" name="bug_file">-->
                        <input type="file" name="task_file">
                        <br><br>
                        <p class="nota_bene">** Les captures d'&eacute;cran (ou autres documents) sont tr&egrave;s appr&eacute;ci&eacute;es et peuvent facilit&eacute; grandement la correction du probl&egrave;me</p>
                        <br>
                        <input type="submit" value="Envoyer">
                    </div>
                </form>
            </div>
            <div id="valide_bug" style="display: none;">
                <img src="/admin/images/icon/erreur.png" class="btn_erreur" onclick="closeFenetre('bug');"><p class="msg_validation"><img src="/admin/images/icon/valider_action.png" class="close_help"> Votre demande a été transmise</p>
            </div>
        </div>
    </div>
    <div id="showtask" style="display: none;">
        <div id="fade" class="close_help_fade"></div> 
        <div id="popup_task_content" class="popup_block">
            <div id="saisie_tache">
                <img src="/admin/images/icon/erreur.png" style="float:right; cursor: pointer;" class="close_help">
                <form method="post" id="form_task" enctype="multipart/form-data">
                    <div id="step2">
                        <p class="step">1 - A qui la t&acirc;che est-elle destin&eacute;e ?</p>
                        <select name="demande_responsable" id="help_responsable">
                            <option value="0">-- Choisir un responsable --</option>
                            <?php foreach($tabResponsable as $le_responsable){ ?>
                            <option value="<?php echo $le_responsable['username']; ?>"><?php echo $le_responsable['nom_contact']; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div style="display: none;" id="steptask3">
                        <p class="step">2 - D&eacute;crivez-la le plus pr&eacute;cis&eacute;ment possible *</p>
                        <input type="text" name="task_libelle" id="task_libelle" placeholder="Libellé court...">
                        <textarea name="task_contenu" id="task_contenu" placeholder="Description détaillée..."></textarea>
                        <p class="step" style="margin-top: 15px;">3 - Eventuellement, fournissez quelques d&eacute;tails compl&eacute;mentaires</p>
						<label for="task_date">Date de fin&nbsp; :</label>
						<input id="task_date" name="task_date" onclick="calendrier_show(this.id,this.value);" style="width:20%;text-align:center;" type="text" placeholder="Choisir une date">
						<br>
						<label for="task_assoc">Associer a :</label>
						<input type="text" id="task_assoc" value="" style="width:15%;text-align:center;"/>
						<span  id="popuptype" style="text-transform:uppercase;"></span>
						<input type="hidden" id="popuplinkid" name="task_link" value=""/>
						<input type="hidden" id="popuplinktype" name="task_type" value=""/>
                        <br>
                        <label for="task_inter">Intervenants :</label>
                        <select name="task_inter" id="task_inter" onchange="add_inter(this.value);">
                            <option value="0">Ajouter...</option>
                            <?php foreach($tabResponsable as $le_responsable){ ?>
                            <option value="<?php echo $le_responsable['username']; ?>"><?php echo $le_responsable['nom_contact']; ?></option>
                            <?php } ?>
                        </select>
                        <br>
                        <span id="popupinter"></span>
                        <br>
						<hr style="width:75%;color:#5897BA;">
						<label for="task_mec">Transmettre sur :</label>
						<input id="task_mec" type="checkbox" value="http://www.mon-espace-cuisson.com/Aci-AdM/" name="remote[Mon Espace Cuisson]"> Mon Espace Cuisson
						<br>
						<br>
                        <input type="file" name="task_file">
						<p style="font-size:10px; text-align:center;"><em>Vous pouvez associer une pi&egrave;ce jointe &agrave; votre requ&ecirc;te *</em></p>
                        <br>
						<br>
                        <input type="submit" value="Envoyer">
                    </div>
                </form>
            </div>
            <div id="valide_tache" style="display: none;">
                <img src="/admin/images/icon/erreur.png" class="btn_erreur" onclick="closeFenetre('tache');"><p class="msg_validation"><img src="/admin/images/icon/valider_action.png" class="close_help"> Votre demande a été transmise</p>
            </div>
        </div>
    </div>
    <?php
		
	}
?>
