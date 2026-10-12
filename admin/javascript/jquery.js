var $jQuery = jQuery.noConflict();

$jQuery(document).ready(function() {

	//**************************//
	//DEBUT FORMULAIRE TACHE/BUG//
	//**************************//
	
	var max_size = $jQuery('#size_bug').val();

	$jQuery('div.help_button').mouseenter(function(e){
		$jQuery(this).animate({
		    top: 0,
		}, 100);
    $jQuery('div.help_button2').animate({
        top: 84,
    }, 1100);
	});
  
  $jQuery('div.help_button').mouseleave(function(e){
		$jQuery(this).delay(4000).animate({
		    top: -62,
		}, 100);
    $jQuery('div.help_button2').delay(3000).animate({
        top: -147,
    }, 1100);
    
	});
    

	//Affichage du bouton des tâches
	$jQuery('a#task_button').click(function(e){
		$jQuery('#showbug').hide();
		$jQuery('#valide_tache').hide();
		$jQuery('#showtask').show();
		$jQuery('#saisie_tache').show();
	});

	//Affichage du bouton des bugs
	$jQuery('a#bug_button').click(function(e){
		$jQuery('#showtask').hide();
		$jQuery('#valide_bug').hide();
		$jQuery('#showbug').show();
		$jQuery('#saisie_bug').show();
	})

	//Masquage du bouton d'aide
	$jQuery('.close_help').click(function(e){
		closeFenetre('bug');
		closeFenetre('tache');
	});

	//Validation champs "Nature de la demande"
	$jQuery('#help_type').on('change',function(e){
		var valeur = $jQuery(this).val();

		if(valeur != 0 && typeof valeur == 'string'){
			$jQuery('#step2').show();
			$jQuery('#step3').hide();
		} else {
			$jQuery('#step2').hide();
			$jQuery('#step3').hide();
		}
	});

	//Validation champs "Responsable"
	$jQuery('#help_responsable').on('change',function(e){
		var valeur = $jQuery(this).val();

		if(valeur != 0 && typeof valeur == 'string'){
			$jQuery('#steptask3').show();
		} else {
			$jQuery('#steptask3').hide();
		}
	});

	//Gestion soumission formulaire
	$jQuery('#form_bug').on('submit',function(e){
            //EmpÃªche le navigateur de soumettre le formulaire
            e.preventDefault();

            var erreur = '';
            var parentcount=location.pathname.split("/").length-3;
            var pageparent="../".repeat(parentcount);


            var $form    = $jQuery(this);
            var formdata = (window.FormData) ? new FormData($form[0]) : null;
            var data     = (formdata !== null) ? formdata : $form.serialize();

                            
            $jQuery.ajax({
                            url: pageparent+'ajax/setBug.php',
                            type: 'post',
                            contentType: false, // obligatoire pour de l'upload
                            processData: false, // obligatoire pour de l'upload
                            dataType: 'json', // selon le retour attendu
                data: data,
                complete: function (response) {
                    if(response.responseText != 'creationok'){
                            alert(response.responseText);
                    } else {
                            $jQuery('#saisie_bug').hide();
                            $jQuery('#valide_bug').show();
                    }
                }
            });
            
		
	});

	//Gestion soumission formulaire
	$jQuery('#form_task').on('submit',function(e){
            //EmpÃªche le navigateur de soumettre le formulaire
            e.preventDefault();

            var erreur = '';
            var parentcount=location.pathname.split("/").length-3;
            var pageparent="../".repeat(parentcount);


            var $form    = $jQuery(this);
            var formdata = (window.FormData) ? new FormData($form[0]) : null;
            var data     = (formdata !== null) ? formdata : $form.serialize();

            $jQuery.ajax({
                            url: pageparent+'ajax/setTask.php',
                            type: 'post',
                            contentType: false, // obligatoire pour de l'upload
                            processData: false, // obligatoire pour de l'upload
                            dataType: 'json', // selon le retour attendu
                data: data,
                complete: function (response) {
                    if(response.responseText != 'creationok'){
                            alert(response.responseText);
                    } else {
                            $jQuery('#saisie_tache').hide();
                            $jQuery('#valide_tache').show();
                    }
                }
            });
		
	});

	//************************//
	//FIN FORMULAIRE TACHE/BUG//
	//************************//
});

function closeFenetre(type){

	switch(type){
		case 'bug':

			$jQuery('#showbug').hide(); 
			$jQuery('#saisie_bug').show();
			$jQuery('#valide_bug').hide();

			//Vidage du formulaire
			$jQuery('#form_bug')[0].reset();
			
			break;
		case 'tache':

			$jQuery('#showtask').hide(); 
			$jQuery('#saisie_tache').show();
			$jQuery('#valide_tache').hide();
			$jQuery('#steptask3').hide();

			//Vidage du formulaire
			$jQuery('#form_task')[0].reset();

			break;
	}
	
}

function export_stats(action, tab=null){
    var prov=valide=marque=from=to=temp='';
    switch (action) {
        case 'vendu':
            var rowsP = document.getElementsByName('prov[]');
            for (var i = 0, l = rowsP.length; i < l; i++) {
                if (rowsP[i].checked) {
                    prov += "&prov[]=" + rowsP[i].value;
                }
            }
            var rowsV = document.getElementsByName('valide[]');
            for (var i = 0, l = rowsV.length; i < l; i++) {
                if (rowsV[i].checked) {
                    valide += "&valide[]=" + rowsV[i].value;
                }
            }
            var marque = document.getElementsByName('marque')[0].value;
            var from = document.getElementsByName('from')[0].value;
            var to = document.getElementsByName('to')[0].value;
            var rowsT = document.getElementsByName('temp');
            if(rowsT.checked)
                temp = 1;
            else
                temp = '';
            var file = 'ArticlesVendus';
            var data = 'action='+action+'&file='+file+'&export='+tab+prov+valide+'&marque='+marque+'&from='+from+'&to='+to+'&temp='+temp;
            ajaxrequest('ajax/stat_export.php?'+data, successExport(data));
            break;

        case 'vu':
        	var from = document.getElementsByName('from')[0].value;
        	var to = document.getElementsByName('to')[0].value;
        	var marque = document.getElementsByName('marqueid')[0].value;
        	var file = 'ArticlesVus';
        	var data = 'action='+action+'&file='+file+'&marque='+marque+'&from='+from+'&to='+to;
        	ajaxrequest('ajax/stat_export.php?'+data, successExport(data));
        	break;
    }
}

function successExport(data){
	//
	document.location.href = 'ajax/stat_export.php?'+data;
}

//Charge une liste de produits en Ajax
function getProduit(txt){
	
	if(txt.length >= 4){

		$jQuery('#resultat_recherche').html(''); //Affichage du loader dans la fenêtre de chargement

		$jQuery.ajax({
			url: 'ajax/getListeProduits.php',
			type: 'get',
            data: { 'artcode' : txt },
            complete: function (response) {
            	$jQuery('#resultat_recherche').html(response); //Suppression du loader + afficher résultat
            }
        });

	}
}

function suivi_copy(select_value, userid, suiviid, type_suivi){

	$jQuery.ajax({
	          
		type : 'GET',
		url : 'ajax/suivi_client_copy.php',
		dataType : 'text',
		data : { 
          	type 	   : select_value,
          	userid     : userid,
          	suiviid    : suiviid,
          	type_suivi : type_suivi
		}

	}).done(function(responseText){

			
	});
	//location.reload(true);
	window.location.href = window.location.href;

}

function CheckListTransferts(select_value, userid, suiviid, type_suivi){
	
	$jQuery('#type_suivi_resultats_'+suiviid).show();
	$jQuery('#type_suivi_resultats_'+suiviid).html('<img src="/images/templates/ajax-loader.gif" style="margin: 25px auto 0;">');

	$jQuery.ajax({
	          
		type : 'GET',
		url : 'ajax/suivi_transfert_liste.php',
		dataType : 'text',
		data : { 
          	type 	   : select_value,
          	userid     : userid,
          	suiviid    : suiviid,
          	type_suivi : type_suivi
		}

	}).done(function(responseText){

		if(responseText != 'vide'){ 
			$jQuery('#type_suivi_resultats_'+suiviid).css('border','none');
			$jQuery('#type_suivi_resultats_'+suiviid).html(responseText);
		} else {
			$jQuery('#type_suivi_resultats_'+suiviid).html('<p style="margin-top: 30px; font-weight: bold;">Aucun résultat</p>');
		}		
	});
}

function CheckListTransferts2(select_value, userid, suiviid, type_suivi,where){
	
	//$jQuery('#type_suivi_resultats_'+suiviid).html('<img src="/images/templates/ajax-loader.gif" style="margin: 25px auto 0;">');

	$jQuery.ajax({
	          
		type : 'GET',
		url : 'ajax/suivi_transfert_liste.php',
		dataType : 'text',
		data : { 
          	type 	   : select_value,
          	userid     : userid,
          	suiviid    : suiviid,
          	type_suivi : type_suivi
		}

	}).done(function(responseText){

		if(responseText != 'vide'){ 
			jQuery('#lier_sav_'+where+'_'+suiviid).show();
			$jQuery('#select_lier_sav_'+where+'_'+suiviid).html(responseText);
		} else {
			jQuery('#lier_sav_'+where+'_'+suiviid).show();
			$jQuery('#lier_sav_'+where+'_'+suiviid).html('<p style="margin-top: 30px; font-weight: bold;">Aucun résultat</p>');
		}		
	});
}

function checkListClients(input_value, userid, suiviid, type_suivi){
	
	$jQuery('#type_suivi_clients_'+suiviid).show();
	$jQuery('#type_suivi_clients_'+suiviid).html('<img src="/images/templates/ajax-loader.gif" style="margin: 25px auto 0;">');

	if(input_value.length >= 3){

                $jQuery.ajax({
		          
			type : 'GET',
			url : 'ajax/suivi_client_liste.php',
			dataType : 'text',
			data : { 
				userid     : userid,
	          	txt        : input_value,
	          	suiviid    : suiviid,
	          	type_suivi : type_suivi
			}

		}).done(function(responseText){

			if(responseText != 'vide'){ 
				$jQuery('#type_suivi_clients_'+suiviid).css('border','none');
				$jQuery('#type_suivi_clients_'+suiviid).html(responseText);
			} else {
				$jQuery('#type_suivi_clients_'+suiviid).html('<p style="margin-top: 30px; font-weight: bold;">Aucun résultat</p>');
			}		
		});
	} else {
		$jQuery('#type_suivi_clients_'+suiviid).hide();
	}	
}

function checkListFacture(input_value,suiviid,type_suivi){
        $jQuery('#type_suivi_resultats_'+suiviid).show();
	$jQuery('#type_suivi_resultats_'+suiviid).html('<img src="/images/templates/ajax-loader.gif" style="margin: 25px auto 0;">');

	if(input_value.length >= 5){
                $jQuery.ajax({
		          
			type : 'GET',
			url : 'ajax/suivi_transfert_liste.php',
			dataType : 'text',
			data : {
                        type:'cmd',
                        facturid     : input_value,
	          	suiviid    : suiviid,
	          	type_suivi : type_suivi
			}

		}).done(function(responseText){

			if(responseText != 'vide'){ 
				$jQuery('#type_suivi_resultats_'+suiviid).css('border','none');
				$jQuery('#type_suivi_resultats_'+suiviid).html(responseText);
			} else {
				$jQuery('#type_suivi_resultats_'+suiviid).html('<p style="margin-top: 30px; font-weight: bold;">Aucun résultat</p>');
			}		
		});
        } else {
		$jQuery('#type_suivi_resultats_'+suiviid).hide();
	}
}

function assocSuivi(userid, destinataire_id, suiviid, type_suivi, origine){

	
	switch(origine){	

		case 'commande':
			var Element  = '#type_suivi_resultats_'+suiviid;
			var confirmation = 'Lier à la commande n°'+destinataire_id;
 			break;

		case 'client':
			var Element = '#type_suivi_clients_'+suiviid;
			var confirmation = 'Lier au client n°'+destinataire_id;
			break;
	}

	if(confirm(confirmation)){

		$jQuery.ajax({
		       
			type : 'GET',
			url : 'ajax/suivi_assoc.php',
			dataType : 'text',
			data : { 
	          	destinataire_id   : destinataire_id,
	          	userid 	   		  : userid,
	          	suiviid    		  : suiviid,
	          	type_suivi 		  : type_suivi, 
	          	origine    		  : origine
			}

		}).done(function(responseText){

			if(responseText == 'done'){
				$jQuery(Element).empty();
				$jQuery(Element).hide();

				loadsuivi(userid, 'all','1');
			} else {
				$jQuery(Element).html('Erreur lors du transfert du suivi');
			}
			
		});
	}

	return false;
}
function lierSuivi(type,typeid,suiviid,where){
	switch(type){
		case 'sav':
			var Element = '#lier_sav_'+where+'_'+suiviid;
			var confirmation = 'Lier au sav n°'+typeid;
		break;
	}

	if(confirm(confirmation)){

		$jQuery.ajax({
		       
			type : 'GET',
			url : 'ajax/lier_suivi.php',
			dataType : 'text',
			data : { 
	          	type   : type,
	          	typeid :typeid,
	          	suiviid:suiviid,
	          	action : 'lier'
			}

		}).done(function(responseText){

			if(responseText == 'done'){
				//$jQuery(Element).empty();
				//$jQuery(Element).hide();
				console.log('done done!!');
				location.reload();
			} else {
				$jQuery(Element).html('Erreur lors du transfert du suivi');
			}
			
		});
	}
	return false;

}
function delierSuivi(type,typeid,suiviid,where){
	switch(type){
		case 'sav':
			var Element = '#lier_sav_'+where+'_'+suiviid;
			var confirmation = 'Lier au sav n°'+typeid;
		break;
	}

	if(confirm(confirmation)){

		$jQuery.ajax({
		       
			type : 'GET',
			url : 'ajax/lier_suivi.php',
			dataType : 'text',
			data : { 
	          	type   : type,
	          	typeid :typeid,
	          	suiviid:suiviid,
	          	action : 'delier'
			}

		}).done(function(responseText){

			if(responseText == 'done'){
				$jQuery(Element).empty();
				$jQuery(Element).hide();
				console.log('done done!!');
				location.reload();
			} else {
				$jQuery(Element).html('Erreur lors du transfert du suivi');
			}
			
		});
	}
	return false;
}
function lierMail(userid, destinataire_id, mailid, sens, origine,where){

	
	switch(origine){	

		case 'sav':
			var Element = '#lier_sav_'+where+'_'+mailid;	
			var confirmation = 'Lier au sav n°'+destinataire_id;
			break;

		case 'commande':
			var Element  = '#type_suivi_resultats_'+mailid;
			var confirmation = 'Lier à la commande n°'+destinataire_id;
 			break;
	}

	if(confirm(confirmation)){

		$jQuery.ajax({
		       
			type : 'GET',
			url : 'ajax/lier_mail.php',
			dataType : 'text',
			data : { 
	          	destinataire_id   : destinataire_id,
	          	userid 	   		  : userid,
	          	mailid    		  : mailid,
	          	sens 		  	  : sens, 
	          	type    		  : origine,
	          	action            : 'lier'
			}

		}).done(function(responseText){

			if(responseText == 'done'){
				$jQuery(Element).empty();
				$jQuery(Element).hide();
				console.log('done done!!');
				loadsuivi(userid, 'all','1');
			} else {
				$jQuery(Element).html('Erreur lors du transfert du suivi');
			}
			
		});
	}

	return false;
}
function affichePjt(id){
	$jQuery('#liaison_projet_'+id).toggle();
}

function delierMail(userid, destinataire_id, mailid, sens, origine){
	switch(origine){	

		case 'sav':
			var Element = '#lier_sav_'+mailid;	
			var confirmation = 'Délier du sav n°'+destinataire_id+' ?';
			break;

		case 'commande':
			var Element  = '#type_suivi_resultats_'+mailid;
			var confirmation = 'Délier de la commande n°'+destinataire_id+' ?';
 			break;
	}

	if(confirm(confirmation)){

		$jQuery.ajax({
		       
			type : 'GET',
			url : 'ajax/lier_mail.php',
			dataType : 'text',
			data : { 
	          	destinataire_id   : destinataire_id,
	          	userid 	   		  : userid,
	          	mailid    		  : mailid,
	          	sens 		  	  : sens, 
	          	type    		  : origine,
	          	action 			  : 'delier'	
			}

		}).done(function(responseText){

			if(responseText == 'done'){
				$jQuery(Element).empty();
				$jQuery(Element).hide();
				console.log('done done!!');
				loadsuivi(userid, 'all','1');
			} else {
				$jQuery(Element).html('Erreur lors du transfert du suivi');
			}
			
		});
	}

	return false;
}

function hideAssoc(suiviid){
	if($jQuery('#type_suivi_resultats_'+suiviid)){
		$jQuery('#type_suivi_resultats_'+suiviid).hide();
		$jQuery('#type_suivi_resultats_'+suiviid).empty();
	}

	if($jQuery('#type_suivi_clients_'+suiviid)){
		$jQuery('#type_suivi_clients_'+suiviid).hide();
		$jQuery('#type_suivi_clients_'+suiviid).empty();
	}	
}

function upd_select(value, type, dest_id)
{
	switch(type)
	{
		case 'fournisseur-marque':
			var ajax_page = 'getMarque.php';
			break;
	}

	$jQuery.ajax(
		{	       
			type 	 : 'GET',
			url 	 : 'ajax/'+ajax_page,
			dataType : 'text',
			data 	 : { param : value }
		}
	).done(function(responseText)
		{
			$jQuery('#'+dest_id).html(responseText);
		}
	);
}

function filtrer_suivi_pjt(projetid) {
	$jQuery('.projet_'+projetid).toggle();
}

function toggle_futur()
{
	$jQuery('.futur').toggle();
}

function toggleDiv(div)
{
	$jQuery(div).toggle();
}

function del_field(id)
{
	//toggle_check('uncheck', 'delete'); //On décoche tout pour ne cocher que celui qui nous intéresse

	$jQuery('.delete').each(function(num)
	{
		var Elem = $jQuery(this);

		if(Elem.val() == id)
		{
			Elem.prop('checked', true);
			toggle_delete(id);
		} 
		else
		{
			if($jQuery('#chk' + Elem.val()).is(':checked'))
			{
				Elem.prop('checked', false);
				delete_from_form(Elem.val());
			} 
		}
	});

	submit_form('delete_elements');
	$jQuery('#chk' + id).prop('checked', false); //Si l'utilisateur a finalement cliqué sur "Annuler", on décoche la case
}

function tri_liste(url, libelle, ordre)
{
	var debut_url = '&';
	if(url == '') debut_url = '?';
	window.location = url + debut_url + 'tri=' + libelle + '&ordre=' + ordre;
}

function toggle_class(elem, classe)
{
	$jQuery(elem).next('.' + classe).toggle();
}

function toggle_check(state, classe)
{
	switch(state)
	{
		case 'check':

			$jQuery('.' + classe).each(function(num)
			{
				$jQuery(this).prop('checked', true);
				toggle_delete($jQuery(this).val());
			});

			break;
		case 'uncheck':

			$jQuery('.' + classe).each(function(num)
			{
				$jQuery(this).prop('checked', false);
				toggle_delete($jQuery(this).val());
			});

			break;
	}
}

function submit_form(form_id)
{
	if(confirm('Supprimer les enregistrements sélectionnés ?')) document.getElementById(form_id).submit();
}

function toggle_delete(value)
{
	if($jQuery('#chk' + value).is(':checked'))
	{
		add_in_form(value);
	}
	else
	{
		delete_from_form(value);
	}
}

function delete_from_form(id)
{
	$jQuery('#delid_' + id).remove();
}

function add_in_form(value)
{
	var form  = $jQuery('#delete_elements');
	var input = document.createElement("input");
	input.type  = 'hidden';
	input.value = value;
	input.name  = 'del_id[]';
	input.id    = 'delid_' + value;

	form.append(input);
}

$jQuery(function(){
	$jQuery("#datepicker").datepicker({
		hangeMonth: true,
        changeYear: true,
		altField: "#datepicker",
		closeText: 'Fermer',
		prevText: 'Précédent',
		nextText: 'Suivant',
		currentText: 'Aujourd\'hui',
		monthNames: ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'],
		monthNamesShort: ['Janv.', 'Févr.', 'Mars', 'Avril', 'Mai', 'Juin', 'Juil.', 'Août', 'Sept.', 'Oct.', 'Nov.', 'Déc.'],
		dayNames: ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'],
		dayNamesShort: ['Dim.', 'Lun.', 'Mar.', 'Mer.', 'Jeu.', 'Ven.', 'Sam.'],
		dayNamesMin: ['D', 'L', 'M', 'M', 'J', 'V', 'S'],
		weekHeader: 'Sem.',
		dateFormat: 'yy-mm-dd'});
});



function validationFacture(){
	var type = $jQuery('#typedefacture').val();
	var option = $jQuery('#action_option').val();
	console.log('validationFacture', type, option);
	if (option === 'refuser' && (type == "trspt" || type == "fourn" || type == "sls")){
		alert("Il existe une commande fournisseur, transporteur et/ou transporteur, veuillez l'annuler.");
	}
	else{
		document.forms.action_cmd.submit();
	}
}

function cloture_demande()
{
	if($jQuery('input[name=precloture]:checked', '#et5form').val())
	{
		if(confirm('Valider la demande de clôture ?'))
		{ 
			document.getElementById('action_cloture').value = 'precloture';  
			document.forms['et5form'].submit(); 
		}
	} 
	else
	{
		alert('Veuillez d\'abord sélectionner une des deux options');
	}
}

function cloture_demande_rapide(){
	if(confirm('Demande cloture : ATTENTION. Seulement pour les dossiers dont le client ne nous a jamais répondu ou envoyé les docs demandés.'))
		{ 
			document.forms['cloture_rapide'].submit();
		}
}

function edit_tag(tag) {
	$jQuery('table').wrap('<form method="post" action="./tags_leads.php"></form>');
	var utm1 = $jQuery('#tagid_'+tag).find('#utm1').html();
	var utm2 = $jQuery('#tagid_'+tag).find('#utm2').html();
	var utm3 = $jQuery('#tagid_'+tag).find('#utm3').html();

	$jQuery('#tagid_'+tag).find('#utm1').html('<input type="hidden" name="action" value="tags_leads"><input type="text" name="utm_source" value="'+utm1+'">');
	$jQuery('#tagid_'+tag).find('#utm2').html('<input type="text" name="utm_medium" value="'+utm2+'">');
	$jQuery('#tagid_'+tag).find('#utm3').html('<input type="text" name="utm_campagne" value="'+utm3+'">');
	$jQuery('#tagid_'+tag).find('#utm4').html('<input type="hidden" name="utm_id" value="'+tag+'"><input type="submit" class="valid_tag" value="Valider modification">');
}

function add_tag() {
	$jQuery('.form_add_tag').show();
}


function mail_existe_color(id,color,search=null) {
	const arr = id.split(",");
	if(search!=null) {
		const arr2 = search.split(",");
		var i = 0;
		arr.forEach(element => {
			var mail = $jQuery('#mail_exist_color_'+element).text();
			$jQuery('#mail_exist_color_'+element).html(mail+' <a href="/admin/utilisateur.php?action=editer&userid='+arr2[i]+'" title="contact associé à un client" target="_blank" style="text-decoration: none">🔍</a>');
			$jQuery('#mail_exist_color_'+element).css('color', color);
			i = i + 1;
		});
	}
	else {
		arr.forEach(element => $jQuery('#mail_exist_color_'+element).css('color', color));
	}
}


function show_prix_fixe(id,val){
	if(val=='add'){
		$jQuery("#prix_fixe_option").show();
		$jQuery("#prix_fixe_option").find('input').prop('required',true);
	}
	else if(val==true){
		$jQuery("#prix_fixe_option_"+id).show();
		$jQuery("#prix_fixe_option_"+id).find('input').prop('required',true);
	}
	else{
		$jQuery("#prix_fixe_option_"+id).hide();
		$jQuery("#prix_fixe_option_"+id).find('input').prop('required',false);
		$jQuery("#prix_fixe_option").hide();
		$jQuery("#prix_fixe_option").find('input').prop('required',false);
	}
}

function zoom(id){
	$jQuery("#"+id).animate({zoom: '200%'}, "slow");
}
function dezoom(id){
	$jQuery("#"+id).animate({zoom: '100%'}, "slow");
}