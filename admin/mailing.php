<?php
require_once($_SERVER['DOCUMENT_ROOT']."/admin/admin_global.php");
require_once(BACK_PATH.'/mailing_config.php');

include_once(BACK_PATH."/header.php");
include_once(BACK_PATH."/menu.php");
?>
<script type="text/javascript" src="../javascripts/host.js"></script>
<script type="text/javascript" src="facture.js"></script>
<script type="text/javascript">
	function affiche_creation(){
		$jQuery('#mailing_produit').show();
	}

	function affiche_apercu(){
 
        var $form = $jQuery('#mailing_form');
        var formdata = (window.FormData) ? new FormData($form[0]) : null;
        formdata.append('apercu',1);
        var data = (formdata !== null) ? formdata : $form.serialize();

        $jQuery.ajax({
            url: "ajax/mailing.php",
            type: $form.attr('method'),
            contentType: false, // obligatoire pour de l'upload
            processData: false, // obligatoire pour de l'upload
            data: data,
            success: function (response) {
                $jQuery('#apercu_contenu').html(response);
				$jQuery('.apercu').css('display','block');
            }
        });
	}

	function close_apercu(){
		$jQuery('#apercu_contenu').empty();
		$jQuery('.apercu').css('display','none');
	}

	function overBloc(obj){
		objet = $jQuery(obj);

		if(objet.css('borderTopWidth') == '2px'){
			if(objet.attr('class') != 'selected'){
				objet.css({
					'border-color': 'grey',
					'border-width': '1px'
				});
			}
		} else {
			objet.css({
				'border-color': '#363E42',
				'border-width': '2px'
			});
		}
	}	

	function checkRadio(obj){
		div = $jQuery(obj);
		input = div.find('input');
		input.prop('checked', true);

		$jQuery('.choix').children().each(function(){
			$jQuery(this).removeClass('selected');
			$jQuery(this).css({
				'border-color': 'grey',
				'border-width': '1px'
			});
		});

		div.attr('class','selected');
	}

	function getProduits(txt){
	    
	    if(txt.length <= 3) return;

	    var url = "marketplace/src.php?txt="+txt+"&active=1&site_code=base";
	    ajaxrequest(url, getProduits_callback);
	    
	    $jQuery('#resultats').css('display','block');
	    $jQuery('#resultats').html('<img src=\"/configurations/<?php echo $host; ?>/images/templates/ajax_wait.gif\">');
	}

	function getProduits_callback(httprequest){

		var xml    = httprequest.responseXML.documentElement;
		var html   = '';
		var vistxt = new Array();

		vistxt[0] = '<br><span style=\"color:red;\">Invisible</span>';
		vistxt[1] = '<br>Visible';
		
		if(xml.getElementsByTagName('prod')[0]){
			
			html += '<table width=\"100%\" style=\"border:solid thin #808080;\">';
			
			for(i=0;i<xml.getElementsByTagName('prod').length;i++){
				
				var description = xml.getElementsByTagName('prod')[i].firstChild.nodeValue;
				var id 			= xml.getElementsByTagName('prod')[i].getAttribute('id');
				var artid		= xml.getElementsByTagName('prod')[i].getAttribute('artid');
				var actif		= xml.getElementsByTagName('prod')[i].getAttribute('active_base');
				var stock 		= xml.getElementsByTagName('prod')[i].getAttribute('stock');
				var marque 		= xml.getElementsByTagName('prod')[i].getAttribute('marque');
				var ref 		= xml.getElementsByTagName('prod')[i].getAttribute('ref');
				var lot	 		= xml.getElementsByTagName('prod')[i].getAttribute('lot');
				var ventes 		= xml.getElementsByTagName('prod')[i].getAttribute('ventes');
				var ventes1an 	= xml.getElementsByTagName('prod')[i].getAttribute('ventes1an');
				var stock 		= 'Pas en stock';

				if(xml.getElementsByTagName('prod')[i].getAttribute('stock')=='1') stock = 'En stock : '+xml.getElementsByTagName('prod')[i].getAttribute('stockqte');
				if(actif == 1){
					html += ""
					html += "<tr id=\"tr_"+artid+"\">"
						html += "<td><img src=\"/configurations/<?php echo $host; ?>/images/produits/br/"+artid+".jpg\" style=\"max-width: 80px; max-height: 80px;\" id=\"articleimg"+artid+"\"></td>"
						html += "<td style=\"border-bottom:solid thin #cccccc;\">"+marque+"<br>"+id+"<br>"+ref+"<br>"+vistxt[actif]+"</td>"
						html += "<td style=\"border-bottom:solid thin #cccccc;\"><b>"+description+"</b><br>Ventes : "+ventes+"<br>Ventes 1 an : "+ventes1an+"<br>"+stock+"</td>"
						html += "<td style=\"border-bottom:solid thin #cccccc;\"><a href='javascript:selectProduit("+artid+",\""+id+"\",\""+description.replace(/\'/g,"&apos;")+"\")'>Sélectionner</a></td>"
					html += "</tr>";
				} 
			}

			html += '</table>';

		} else {
			$jQuery('#resultats').html('Aucun résultats');
		}

		$jQuery('#resultats').html(html);
	}

	function selectProduit(artid,artcode,libelle){

		$jQuery('#resultats').insert
		
		if(artid != undefined){

			html = "<div style=\"float: left; border: 1px solid grey; width: 175px; height: 150px; padding: 5px; margin: 5px;\">";
				html += "<input type=\"hidden\" name=\"mailing_produits[]\" value=\""+artid+"\">"
				html += "<img src=\"/configurations/<?php echo $host; ?>/images/produits/br/"+artid+".jpg\" width=\"50\" style=\"float: left; margin: 0px 10px 5px 0px;\">"
				html += "<img src=\"images/supprimer.gif\" onclick=\"supprProduit();\" style=\"float: right; cursor: pointer;\">";
				html += "<p style=\"float: left; padding: 0; margin: 0 0 10px; width: 100x; text-align: left; font-weight: bold;\">"+artcode+"</p>"
				html += "<p style=\"float: left; padding: 0; margin: 0; width: 110px;\">"+libelle+"</p>";
			html += "</div>";
			
			$jQuery(html).insertBefore('#resultats');

			$jQuery('#resultats').find('table').remove();

			/*document.getElementById('article'+articleid).innerHTML="<img src=\"/configurations/www.ma-cave-a-vin.fr/images/produits/br/"+artid+".jpg\" width=\"50\"> <img src=\"images/supprimer.gif\" onclick=\"articleid="+articleid+"; select_article_mail();\">";
			mail_articles[articleid]=artid;
			document.getElementById('srcarticle'+articleid).value=artcode;
			document.getElementById('article_mail_result').innerHTML="";*/
		} else {
			document.getElementById('article'+articleid).innerHTML="";
			mail_articles[articleid]=undefined;
			document.getElementById('srcarticle'+articleid).value="";
		}
	}

</script>

<style type="text/css">
	div.apercu { z-index: 9004; position: absolute; top: 0; left: 0; width: 100%; height:100%; text-align: center; display: none; }
	div.opacite { position: fixed; top: 0; left: 0; width: 100%; height:100%; background: #000; opacity: 0.7; }
	div.apercu_contenu { position: relative; width: 700px; background-color: #fff; margin: 100px auto 0; }
	div.choix { display: block; text-align: center; width: 100%; }
	div.choix div { border: 1px solid grey; display: inline-block; margin: 0 15px; text-align: center; width: 250px; cursor: pointer; }
	div.choix div.selected { border: 2px solid #363E42 !important; }
	div p.sous_titre { display: block; margin: 0; padding: 10px 0; color: green; font-size: 16px; font-weight: bold; }
	.titre_form { display: block; text-align: center;  }
	.titre_form p { background-color: #363e42; width: 600px; font-weight: bold; margin: 0; padding: 2px 0; color: #cccccc; font-size: 14px; }
	.valeur_form { display: block; padding: 5px 0 10px 0; text-align: center; }
	.valeur_form input[type="text"] { width: 600px; }
	.valeur_form input[type="checkbox"] { vertical-align: -2px; }
	.valeur_form input[type="button"] { width: 130px; display: inline; }
	.valeur_form textarea { width: 600px; height: 100px; }
	div.valeur_form .main { float: left; }
	div.valeur_form .childs { width: 200px; float: left; text-align: left; }
	.global { font-family: Verdana; width: 1000px;  }
	.global2 { width: 600px; margin: 0 auto; text-align: center; }
</style> 

<h1>Mailing</h1>
<form method="post" action="" enctype="multipart/form-data" id="mailing_form">
	
	<div class="apercu">
		<div class="opacite" onclick="close_apercu();"></div>
		<div class="apercu_contenu" id="apercu_contenu"></div>
	</div>

	<div class="global">
		<div class="global2">
			<div class="titre_form"><p>Texte d'entête</p></div>
			<div class="valeur_form"><input type="text" name="mailing_entete" id="mailing_entete" value="<?php isset($_POST['mailing_entete']) && !empty($_POST['mailing_entete']) ? $_POST['mailing_entete'] : ''; ?>" placeholder="Ex : Promotions pour la rentrée. Découvrez notre sélection d’idées cadeaux jusqu’à -30% !"></div>
			<div class="titre_form"><p>Bannière</p></div>
			<div class="valeur_form"><input type="file" name="mailing_banniere" id="mailing_banniere"></div>
			<div class="titre_form"><p>Titre</p></div>
			<div class="valeur_form"><input type="text" name="mailing_titre" id="mailing_titre" value="<?php isset($_POST['mailing_titre']) && !empty($_POST['mailing_titre']) ? $_POST['mailing_titre'] : ''; ?>" placeholder="Ex : Promotions de rentrée"></div>
			<div class="titre_form"><p>Description</p></div>
			<div class="valeur_form"><textarea name="mailing_desc" id="mailing_desc" placeholder="Ex : Promotions de rentrée"><?php isset($_POST['mailing_desc']) && !empty($_POST['mailing_desc']) ? $_POST['mailing_desc'] : ''; ?></textarea></div>
			<div class="titre_form"><p>Lien vers l'offre</p></div>
			<div class="valeur_form"><input type="text" name="mailing_lien" id="mailing_lien" value="<?php isset($_POST['mailing_lien']) && !empty($_POST['mailing_lien']) ? $_POST['mailing_lien'] : ''; ?>" placeholder="Ex : http://<?php echo $host; ?>/vente_flash.html"></div>
			<div class="titre_form"><p>Produit(s)</p></div>
			<p class="sous_titre">Type d'affichage</p>
			<div class="choix">
				<div onmouseover="overBloc(this);"  onmouseout="overBloc(this);" onclick="checkRadio(this);">
					<input type="radio" name="template_pdt_type" value="1" style="display: block; margin: 5px auto 10px;">
					<img src="<?php echo BACK_URL.'/images/mailing_alterne.jpg'; ?>" style="width: 200px;">
				</div>
				<div onmouseover="overBloc(this);" onmouseout="overBloc(this);" onclick="checkRadio(this);">
					<input type="radio" name="template_pdt_type" value="2" style="display: block; margin: 5px auto 10px;">
					<img src="<?php echo BACK_URL.'/images/mailing_fixe.jpg'; ?>" style="width: 200px;">
				</div> 
			</div>
			<div style="clear: both;"></div>
			<p class="sous_titre">Produits</p>
			<div class="valeur_form"><input type="button" value="Ajouter" onclick="affiche_creation();"> <input type="text" name="mailing_produit" id="mailing_produit" placeholder="Ex : ART200" style="display: none; width: 100px;" onkeyup="getProduits(this.value);"></div>
			<div id="resultats"></div>
			<br><br>
			<div class="titre_form"><p>Catégorie(s)</p></div>
			<div class="valeur_form">
					<?php 
					$i = 0;
					foreach($aCategories as $num => $lacategorie){
						if($i == 0 || $i%3 == 0) echo "<div class=\"main\">";
						echo "<div class=\"childs\"><input type=\"checkbox\" name=\"mailing_categories[]\" value=\"".$num."\"> ".$lacategorie['intitule']."</div>";
						$i++;
						if($i%3 == 0 || $i == count($aCategories)) echo "</div>";
					}
					?>
			</div>

			<input type="button" value="Aperçu" onclick="affiche_apercu();">
		</div>
	</div>
</form>