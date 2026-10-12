<?php
$include_retard = true;

if($include_retard){
	include($rootpath.'/admin/includes/temp/last_relance.php');
	?>
	<script type="text/javascript">

  		function checkRelance(){

  			var email_id = document.form_relance.email_relance.value;
  			if(email_id == 0){
  				alert('Veuillez choisir un email type');
  				return false;
  			}

  			var statut_id = document.form_relance.statut_relance.value;
  			if(statut_id == -2){
  				alert('Veuillez choisir un statut');
  				return false;
  			}

  			return true;

  			/*var reponse = confirm('Etes-vous certain de vouloir relancer le client ?');
			if(reponse){
			    return true;
			} else {
				return false;
			}*/
		}

		/*function affiche_apercu(){

			var email_id = document.form_relance.email_relance.value;
  			if(email_id == 0){
  				alert('Veuillez choisir un email type');
  				return false;
  			}

  			window.open("includes/apercu_relance.php?utilisateur_id=<?php echo $utilisateur['userid']; ?>&email_id="+email_id,"Aperçu de l'email de relance rapide","directories=no, menubar=no, status=no, location=no, scrollbars=yes, top=100, left=100, width=800, height=800");
		}*/

		/*function post_popup(myForm){

			window.open("includes/apercu_relance.php","Aperçu de l'email de relance rapide","directories=no, menubar=no, status=no, location=no, scrollbars=yes, top=100, left=100, width=800, height=800");
			//form.target = 'Popup_Window';
		}*/

     </script>
	<?php
}

$leCss = "css_btn_tracking(this);";
//recuperation des projets
$filtreprojet="";
$choixprojet = "<span id=\"cd_pjt\" class=\"cadreenvoi\" onclick=\"$leCss reset_btn_tracking(this.id);\">Projet <select name=\"choixprojet\" onchange=\"cadreenvoi('PJT',this.value)\"><option value=\"\"></option>";
$nb_projet_client1=$DB_site2->query_first("SELECT COUNT(*) FROM projet WHERE userid='".$utilisateur['userid']."' AND etatid NOT IN(5,6) ");
$nb_projet_client2=$DB_site2->query_first("SELECT COUNT(*) FROM projet AS p INNER JOIN projet_relation AS pr USING(projetid) WHERE pr.userid='".$utilisateur['userid']."' AND p.etatid NOT IN(5,6)");
if($nb_projet_client1[0]>0 || $nb_projet_client2[0]>0){
	
	$filtreprojet .= "<div class=\"filtreprojet\"> Filtrer :";

	$sql = "SELECT * FROM (
							(SELECT p.* FROM projet AS p WHERE userid='".$utilisateur['userid']."' )
							UNION
							(SELECT p.* FROM projet AS p INNER JOIN projet_relation AS pr USING(projetid) WHERE pr.userid='".$utilisateur['userid']."' )
						  )
						 AS T ORDER BY T.libelle ASC ";
	$projet_clients=$DB_site2->query($sql);
	while($projet_client=$DB_site2->fetch_array($projet_clients)){
		$filtreprojet .= "<span class=\"suiviprojet\" style=\"display:block;\"><input type=\"checkbox\" id=\"filterprojet_".$projet_client['projetid']."\" onclick=\"filtrer_suivi_pjt(".$projet_client['projetid'].")\" checked> Projet ".$projet_client['projetid']." : '".$projet_client['libelle']."'</span>";
		$choixprojet.="<option value=\"".$projet_client['projetid']."\">PJT$projet_client[projetid]-$projet_client[userid] ".$projet_client['libelle']."</option>";
	}
	$filtreprojet .= "</div>";
	
}else{
	$choixprojet .="<option value=\"0\">SANS PROJET</option>";
}
$choixprojet .="</select></span><br>";
//recuperation des commandes + sav
$choixcde ="";
$choixsav ="";
$lesSav = array();
//$choixcpt ="";

$factures = $DB_site2->query("SELECT facturid FROM facture WHERE userid='".$utilisateur['userid']."' AND valide in(1,5,7,8) ORDER BY facturid DESC");
if($DB_site2->num_rows($factures)>0){
	$choixcde .= "<span id=\"cd_cde\" class=\"cadreenvoi\" onclick=\"$leCss reset_btn_tracking(this.id);\">CDE : <select name=\"choixcde\" onchange=\"cadreenvoi('CDE',this.value)\"><option value=\"\"></option>";
	//$choixcpt .="<span id=\"cd_cpt\" class=\"cadreenvoi\" onclick=\"$leCss reset_btn_tracking(this.id);\">CPT : <select name=\"choixcpt\" onchange=\"cadreenvoi('CPT',this.value)\"><option value=\"\"></option>";
	while($facture = $DB_site2->fetch_array($factures)){
		$choixcde .="<option value=\"".$facture['facturid']."\">".$facture['facturid']."</option>";
		//$choixcpt .="<option value=\"".$facture['facturid']."\">".$facture['facturid']."</option>";

		$savs = $DB_site2->query("SELECT id FROM sav WHERE facturid='".$facture['facturid']."' AND fin=0");
		if($DB_site2->num_rows($savs)>0){
			
			while ($sav = $DB_site2->fetch_array($savs)) {
				
				$lesSav[] = $sav['id'];
			}	
		}
	}
	$choixcde .="</select></span><br>";
	//$choixcpt .="</select></span>";
} 

if(count($lesSav) > 0){
	$choixsav .="<span id=\"cd_sav\" class=\"cadreenvoi\" onclick=\"$leCss reset_btn_tracking(this.id);\">SAV <select name=\"choixsav\" onchange=\"cadreenvoi('SAV',this.value)\"><option value=\"\"></option>";
	foreach ($lesSav as $savid) {
		$choixsav .= "<option value=\"".$savid."\">".$savid."</option>";
	}
	$choixsav .="</select></span><br>";
}

print ""
. "<div class=\"tab_special\" id=\"utilisateur_suiviclient\">"
     . "<h2>Suivi client <small>".$utilisateur['langue']."</small> <img src=\"images/drapeaux/".$utilisateur['langue'].".gif\" width=\"20\"></h2>";

print "<div class=\"clear\"></div>";
     


     $pouruser="<option value=\"\">Persone</option>";
     $requete=$DB_site2->query("SELECT username FROM parametresgeneraux");
     while($adminuser=$DB_site2->fetch_array($requete))
         $pouruser.="<option value=\"$adminuser[0]\"".($adminuser[0]==$user_info['username']?" selected":"").">".$adminuser[0]."</option>";



     $opt_mail="<option></option>";
     $req=$DB_site2->query("SELECT emailid,libelle FROM emails_clients WHERE service LIKE 'commercial' AND site_code='".$utilisateur['site_code']."' AND archive = 0");
     while($mail=$DB_site2->fetch_array($req)){
         $opt_mail.="<option data-service=\"commercial\" value=\"".$mail['emailid']."\">".$mail['libelle']."</option>";
     }

     $opt_univers="<option></option>";
     $universurl="";
/*     $req=$DB_site2->query("SELECT * FROM univers");
     while($un=$DB_site2->fetch_array($req)){
         $opt_univers.="<option value=\"".$un['id']."\">".$un['libelleFR']."</option>";
         $universurl.="<input type=\"hidden\" id=\"universurl".$un['id']."\" value=\"".$un['url'.$utilisateur['langue']]."\">";
     }*/


     $prop_av="";
     $c=0;
     $sql="SELECT artid,artcode,prix,active,libelleFR FROM article a INNER JOIN article_vu USING(artid) WHERE userid=".$_GET['userid']." AND active='1'";
     $dateinscr=new DateTime($utilisateur['dateinscription']);
     $archdate=new DateTime("6 month ago");
//      if($archdate>$dateinscr) $sql.=" UNION SELECT * FROM article_vu_arch WHERE userid='".$_GET['userid']."'";
     $sql.=" ORDER BY date DESC LIMIT 20";
     $req=$DB_site2->query($sql);
     while($art=$DB_site2->fetch_array($req)){
         if($c==4){
	    $c=0;
	    $prop_av.="<div class=\"clear\"></div>";
	 }
	 $c++;
         $prop_av.="<div class=\"prop_vignette\">".vignette($art)."<a href=\"javascript:add_prop(".$art['artid'].",'".$art['artcode']."','".addslashes($art['libelleFR'])."')\">Ajouter</a></div>";
     }
     if($DB_site2->num_rows($req)==0) $prop_av="Aucun article trouvé";
     
     $prop_prop="";
     $sql = "SELECT a.*";
     $sql .= " FROM user_prop INNER JOIN article a USING(artid)";
     $sql .= " WHERE userid = ".$utilisateur['userid'];
     $req = $DB_site2->query($sql);

     $c=0;
     while($art = $DB_site2->fetch_array($req)){
	if($c==4){
	    $c=0;
	    $prop_prop.="<div class=\"clear\"></div>";
	}
	$c++;
	$prop_prop.="<div class=\"prop_vignette\">".vignette($art)."<a href=\"javascript:add_prop(".$art['artid'].",'".$art['artcode']."','".addslashes($art['libelleFR'])."')\">Ajouter</a></div>";
     }
     $prop_prop.="<div class=\"clear\"></div>";
     if($DB_site2->num_rows($req)==0) $prop_prop="Aucun article trouvé";
     $expadv = "";
     //Si albane, on lui laisse le choix de l'exp ADV
     if($user_info['username'] == 'albane'){
     	$expadv = 'adv_albane';
     }
     $list_contact=$DB_site2->query("SELECT * FROM admin_contact WHERE username IN ('".$user_info['username']."','contact') AND site_code='".$utilisateur['site_code']."'");
     while($contact=$DB_site2->fetch_array($list_contact)){
	$opt_exp.="<option value=\"".$contact['username']."\" data-mail=\"".$contact['mail'.$utilisateur['langue']]."\"".($contact['username']==$user_info['username']?" checked":"").">".$contact['username']."</option>";
     }
     
     $siteurl=$DB_site2->query_first("SELECT * FROM traduction WHERE path='/site/url'");

	
     
     
     $callid="";
    $suividivdisplay="none";
    $suivititle="";
    $suivimoyen="";
    $suivitype="";
    $suividest="";
	/*
     $cocktel=new cocktel("http://acilyon.cocktel.fr",$user_info['username'],$user_info['cocktelpass'],"json","/tmp");
     if(!is_null(json_decode($cocktel->AppelsEnCours(true)))){
     $json=json_decode($cocktel->AppelsEnCours(true));
	    
	    foreach($json->phones as $phone){
	        foreach($phone->calls as $call){
	            foreach($call->url as $sync=>$syncobj){
	                if(in_array($sync,array("clients mcav","assoc mcav")) && $syncobj->syncid==$utilisateur['userid']){
	                    if($call->local->num==$user_info['telFR'] || $call->local->num==$user_info['telEN']){
	                        $callid=$call->callid;
	                        $suividivdisplay="block";
	                        $suivititle=$call->dir==0?"Appel téléphone client":"Appel client reçu";
	                        $suivimoyen=$call->dir==0?"tel_out":"tel_in";
	                        $suivitype="client";
	                        $suividest=$utilisateur['userid'];
	                        break 3;
	                    }
	                }
	            }
	        }
	    }
	}
	*/
     
	echo ""
	. "<form style=\"width: 750px; float: left;\" name=\"suivi_client\" action=\"utilisateur.php?action=editer&userid=".$utilisateur['userid']."\" method=\"post\">"
		. "<input type=\"hidden\" name=\"moyen\" value=\"".$suivimoyen."\">"
		. "<input type=\"hidden\" name=\"type\" value=\"".$suivitype."\">"
		. "<input type=\"hidden\" name=\"callid\" value=\"".$callid."\">"
		. "<input type=\"hidden\" name=\"destinataire\" value=\"".$suividest."\">"
		. "<table id=\"suiviaction\">"
			. "<thead>"
				. "<tr>"
					. "<td class=\"suiviclient\">Facturation client</td>"
					. "<td class=\"suiviclient\">client</td>"
					. "<td class=\"suivipro\">Fournisseur</td>"
					. "<td class=\"suiviint\">Interne</td>"
				. "</tr>"
			. "</thead>"
			. "<tr>"
				. "<td class=\"suiviclient\"><span class=\"suivilink\" onclick=\"show_choix_suivi(this,'ficheinfo')\" style=\"color:blue;\"><img src=\"images/ring_phone_2.gif\"/>".$utilisateur['telephone']."</span>
				<span class=\"addsuivi\">
    <span class=\"close\" onclick=\"close_choix_suivi(this)\">X</span>
    <span class=\"suivilink\" onclick=\"close_choix_suivi(this);suiviauto('tel_out','client',$utilisateur[userid],'Appel téléphone client',".($usertel?"'$utilisateur[telephone]'":"undefined").");epigy_call('".$utilisateur['telephone']."','".($utilisateur['langue']=='FR'?$user_info['telFR']:$user_info['telEN'])."');\">Click to call</span>
    <span class=\"suivilink\" onclick=\"copy(this)\">Copier</span></span></td>"
				. "<td class=\"suiviclient\" align=\"center\" rowspan=\"3\">"
					. "<input type=\"text\" onkeyup=\"srcfournisseur(this.value,'srcclient');\">"
				. "</td>"
				. "<td class=\"suivipro\" align=\"center\" rowspan=\"3\">"
					. "<input type=\"text\" onkeyup=\"srcfournisseur(this.value,'srcfournisseur');\">"
				. "</td>"
				. "<td class=\"suiviint\" align=\"center\" rowspan=\"3\"><span class=\"suivilink\" onclick=\"close_choix_suivi(this);suiviauto('','',0,'Suivi interne')\">Message interne</span></td>"
			. "</tr>"
			. "<tr><td><div id=\"srcclient\"></div></td></tr>"
			. "<tr><td><div id=\"srcfournisseur\"></div></td></tr>"
			. "<tr>"
				. "<td class=\"suiviclient\"><span class=\"suivilink\" onclick=\"close_choix_suivi(this);suiviauto('mail_out','client',$utilisateur[userid]);mail_suivi('suivi_client',".$utilisateur['userid'].");\">".$utilisateur['mail']."</span></td>"
			. "</tr>"
			. "<tr>"
				. "<td class=\"suiviclient\"><span class=\"suivilink\" onclick=\"close_choix_suivi(this);suiviauto('tel_in','client',$utilisateur[userid],'Appel client reçu');\">Appel reçu</span> <span class=\"suivilink\" onclick=\"close_choix_suivi(this);suiviauto('mail_in','client',$utilisateur[userid],'Email client reçu');\">Email reçu</span></td>"
			. "</tr>"
		. "</table>";
		
	//ENCART RAPPELS
	echo "" 
	. "<div id=\"suividiv\" style=\"display:$suividivdisplay; width: 980px; margin-left: 10px; margin-top:30px;\">" 
		. "<div style=\"float:left;\">" 
			. "<div id=\"suivititle\">".$suivititle."</div>" 
			. "<textarea name=\"suivi\" id=\"suivi\" cols=\"70\" rows=\"14\"></textarea>";
	echo "</div>";
		?>
		<div style="float: left; background: rgb(204, 204, 204) none repeat scroll 0% 0%; padding: 10px; width: 380px; margin: 10px 0px 0px 15px;">
		<?php
		$liste_etats_encours=array(1,2,3,7,8,9);
		$js_jq_etat="if(\$jq(\"select[name='projetid']\").val()=='0'){
								\$jq(\"input[type='radio'][name='etat']\").prop('checked', false);
							  }";

	$i_date_rappel=3;
	$i_bouton_submit=4;
	

    echo    "<br><input type=\"submit\" value=\"Ajouter le suivi\">";
	
	echo "</div> 
		<div class=\"clear\"></div>";
?>
<script>
$jq(document).ready(function () {
	
	$jq("input[type='radio'][name='etat']").click(function() {
		$jq('#span_3').show();
		if( $jq(this).val()==6 || $jq(this).val()==5 || $jq("select[name='projetid']").val()=='0'){
			$jq('#span_4').show();
			$jq('#span_3').hide();
		} else {
			$jq('#span_3').show();
			$jq('#span_4').hide();
		}
		if($jq(this).val()==5 && $jq("select[name='projetid']").val()!='0'){ // en commande et avec projet => on affiche le numéro de commande
			$jq('#span_cde').show();
		} else {
			$jq('#span_cde').hide();
		}
	});
	
	$jq("select[name='projetid']").change(function() {
		if($jq("select[name='projetid']").val()!='-1'){
			$jq('#span_2').show();
		} else {
			$jq('#span_2').hide();
		}
		<?php
		echo $js_jq_etat;
		?>
		$jq('#span_3').hide();
		$jq("input[type='radio'][name='etat']").each(function(){
			if ($jq(this).is(":checked")){
				$jq('#span_3').show();
			}
		});
		if($jq("input[type='radio'][name='etat']").val()==6){
			$jq('#span_4').show();
		}
		if($jq("input[type='radio'][name='etat']").val()==5){
			$jq('#span_4').show();
		}
	});
	$jq("input[type='text'][name='rappel']").click(function() {
		$jq('#span_4').show();
	});
});
</script>
<?php

	echo "" 
	. "</div>" 
	. "<div id=\"suivimaildiv\" style=\"display:none;\">" 
		. "<input type=\"hidden\" id=\"langue\" value=\"".$utilisateur['langue']."\">" 
		. "<input type=\"hidden\" id=\"siteurl\" value=\"".$siteurl[$utilisateur['langue']]."\">" 
		. "<input type=\"hidden\" id=\"site_code\" value=\"".$utilisateur['site_code']."\">" 
		. "<input type=\"hidden\" id=\"pro\" value=\"".($utilisateur['raisonsociale']==""?0:1)."\">" 
		. "<input type=\"hidden\" id=\"dest\" value=\"".$utilisateur['mail']."\">" 
		. "<input type=\"hidden\" id=\"tracking_type\" value=\"CLI\" >"
		. "<input type=\"hidden\" id=\"tracking_id\" value=\"".$utilisateur['userid']."\">"
		. "<input type=\"hidden\" id=\"tracking_chk\" value=\"0\">"
		. "<input type=\"hidden\" id=\"userid_suivi\" value=\"".$utilisateur['userid']."\">"
		. $universurl
	. "<div id=\"propositions_tab\">
	      <div id=\"td_prop_av\" onclick=\"prop_tab('prop_av');\"><img src=\"images/plus.jpg\">Articles vus</div>
	      <div id=\"prop_av\">$prop_av</div>
	      
	      <div id=\"td_prop_prop\" onclick=\"prop_tab('prop_prop');\"><img src=\"images/plus.jpg\">Propositions d'articles</div>
	      <div id=\"prop_prop\">$prop_prop</div>
	  </div>
	  <div id=\"article_mail_result\" style=\"z-index: 100;\"></div>
	  
       
	  <div class=\"mail_com\">
	      <p>1. Choix de l'expéditeur :</p>
	      <select id=\"com_exp\">$opt_exp</select>
	  </div>

	  <div class=\"mail_com\">
	      <p>2. cadre d'envoi :</p>
	      <span id=\"cd_cli\" class=\"cadreenvoi\" onclick=\"cadreenvoi('CLI',".$utilisateur['userid'].");$leCss reset_btn_tracking(this.id);\">Client</span><br>
	      $choixprojet
	      $choixcde
	      $choixsav
	     <!-- <span id=\"cd_cpt\" class=\"cadreenvoi\" onclick=\"cadreenvoi('CPT',".$utilisateur['userid'].");$leCss reset_btn_tracking(this.id);\">Comptabilité</span>-->
	  </div>
	  
	  <div class=\"mail_com\">
	      <p>3. Sujet du mail :</p>
	      <select id=\"com_mail_id\">$opt_mail</select>
	  </div>

	  <div class=\"mail_com\">  
	    <p>4. Notre offre - Bon de réduction :</p>
	    <input type=\"text\" id=\"codepromo\" onkeyup=\"codepromo_src(this.value)\" onclick=\"this.value='';\" value=\"Rechercher\"> <span id=\"selected_codepromo\" style=\"border:solid 1px black;padding:4px;display:none;\"><input type=\"text\" id=\"codepromo_code\" style=\"border:none;width:140px;\" readonly> <img src=\"images/supprimer.gif\" onclick=\"select_codepromo();\"></span> <a href=\"cadeau.php?action=ajouter\" target=\"blank\">Créer un code</a>
	    <div id=\"bon_result\"></div>
	  </div>
	  
	  <div class=\"mail_com\">
	    <p>5. La sélection</p>
	    <input type=\"text\" id=\"srcarticle0\" onkeyup=\"article_mail(this.value,0)\" size=\"10\" onclick=\"move_prop(this)\">
	    <div id=\"article0\"></div>
	  </div>
	  
	  <div class=\"mail_com\">
	    <p>6. Produits conseillés</p>
	    
	    <div class=\"article\">
	      Article 1 :
	      <br><input type=\"text\" id=\"srcarticle1\" onkeyup=\"article_mail(this.value,1)\" size=\"10\" onclick=\"move_prop(this)\">
	      <div id=\"article1\"></div>
	    </div>
	    
	    <div class=\"article\">
	      Article 2 :
	      <br><input type=\"text\" id=\"srcarticle2\" onkeyup=\"article_mail(this.value,2)\" size=\"10\" onclick=\"move_prop(this)\">
	      <div id=\"article2\"></div>
	    </div>

	    <div class=\"clear\"></div>
	    <hr>
	    
	    <div class=\"article\">
	      Article 3 :
	      <br><input type=\"text\" id=\"srcarticle3\" onkeyup=\"article_mail(this.value,3)\" size=\"10\" onclick=\"move_prop(this)\">
	      <div id=\"article3\"></div>
	    </div>
	    
	    <div class=\"article\">
	      Article 4 :
	      <br><input type=\"text\" id=\"srcarticle4\" onkeyup=\"article_mail(this.value,4)\" size=\"10\" onclick=\"move_prop(this)\">
	      <div id=\"article4\"></div>
	    </div>
	    
	    <div class=\"clear\"></div>
	    <hr>
	    
	    <div class=\"article\">
	      Article 5 :
	      <br><input type=\"text\" id=\"srcarticle5\" onkeyup=\"article_mail(this.value,5)\" size=\"10\" onclick=\"move_prop(this)\">
	      <div id=\"article5\"></div>
	    </div>
	    
	    <div class=\"article\">
	      Article 6 :
	      <br><input type=\"text\" id=\"srcarticle6\" onkeyup=\"article_mail(this.value,6)\" size=\"10\" onclick=\"move_prop(this)\">
	      <div id=\"article6\"></div>
	    </div>
	    
	    <div class=\"clear\"></div>
	  </div>
	  	  
	  <div class=\"mail_com\">
	    <p>7. Pourquoi nous choisir</p>
	    <input type=\"checkbox\" id=\"pourquoi\" checked> Afficher
	  </div>
	  
	  <div class=\"mail_com\">
	    <p>8. Nos marques</p>
	    <input type=\"checkbox\" id=\"marques\" checked> Afficher
	  </div>
	  
	  <div class=\"mail_com\">
	      <p>9. Bas de page</p>
	      <select id=\"footer\">
		  <option value=\"complet\" selected>Complet</option>
		  <option value=\"reduit\">Réduit</option>
		  <option value=\"\">Aucun</option>
	      </select>
	  </div>
	  
	  <div class=\"mail_com\">
	      <input type=\"button\" value=\"Générer le mail\" onclick=\"com_mail_generate();\">
	  </div>
    </div>
       </form>";

    $sql = "SELECT *
    		FROM emails_clients
    		WHERE 1
    		AND service LIKE 'commercial'
    		AND email_relance = 1
    		AND archive = 0
    		ORDER BY site_code ASC, sujetFR ASC
    ";
    $reqMails = $DB_site2->query($sql);
    
    $tabArticle=array();
    //Récupération de la liste des articles vus
     $sql = "SELECT DISTINCT(artid)";
     $sql .= " FROM article_vu";
     $sql .= " WHERE userid = ".$utilisateur['userid'];
     $sql .= " ORDER BY nb DESC";
     $sql .= " LIMIT 0,6";
     $req = $DB_site2->query($sql);
     $search_config = true;
     if($DB_site2->num_rows($req) > 0){
          $search_config = false;
          while($row = $DB_site2->fetch_array($req)){
               $tabArticle[] = $row['artid'];
          }
     }
     
     //Si aucun article vu, on récupère les articles du configurateur
     if($search_config){
          //Récupération de la liste des articles des configurations
          $sql = "SELECT artids";
          $sql .= " FROM configurateur";
          $sql .= " WHERE userid = ".$utilisateur['userid'];
          $sql .= " AND artids != ''";
          $sql .= " ORDER BY date DESC";
          $sql .= " LIMIT 0,1";
          $tabResult = $DB_site2->query_first($sql);
          if(is_array($tabResult) && count($tabResult) > 0){
               $tabArticle = array_slice(unserialize($tabResult['artids']),0,6);
          }
     }
    
    ?>
    <form action="utilisateur.php?action=editer&userid=<?php echo $utilisateur['userid']; ?>" name="form_relance" method="post" id="relance_rapide" style="margin-top: -30px;">
	<input type="hidden" name="suivi" value="Relance rapide">
	<input type="hidden" name="moyen" value="">
	<input type="hidden" name="type" value="">
  
    <?php //---------------------- modif TSI, empeche une erreur quand pas d'article vu ou de configurateur ---------------------- 

      if (count($tabArticle) >0)
      {
    ?>
  	  	<input type="hidden" name="relance_artids" value="<?php echo implode(",",$tabArticle); ?>">
    <?php 
      }else{ 
    ?>
    	<input type="hidden" name="relance_artids" value="">
    <?php
	  }
    ?>
    	<p class="title">Relance rapide</p>
    	<div class="relance">
    		<select name="email_relance">
	    		<option value="0">Choisir un email type...</option>
	    		<?php while($l_email = $DB_site2->fetch_array($reqMails)){ ?>
	    		<option value="<?php echo $l_email['emailid']; ?>"><?php echo $l_email['libelle']; ?></option>
	    		<?php } ?>
	    	</select>
	    	<select name="etat" id="etat_rapide">
	    		<option value="-2">Choisir un statut...</option>
	    		<option value="-1">Ne pas mettre à jour</option>
	    		<option value="0">Perdu</option>
	    		<option value="1">A suivre</option>
				<option value="2">Attente info client</option>
				<option value="3">A chiffrer</option>
	    		<option value="4">Dossier sous-traité</option>
	    		<option value="5">En commande (plus de suivi)</option>
	    	</select>
	    	<div class="boutons"><input type="button" value="Aperçu avant envoi" onclick="com_rapide_mail_generate()"></div>
    	</div>
    </form>
	
    <script type="text/javascript">
    	var myForm = document.getElementById('relance_rapide');
		myForm.onsubmit = function() {
			if(!checkRelance()) return false;
			var w = window.open('about:blank','Popup_Window','directories=no, menubar=no, status=no, location=no, scrollbars=yes, top=100, left=100, width=800, height=800');
			this.target = 'Popup_Window';
			this.submit();
		};
	</script>

	<?php
	$options="";
	$date_select =0;
	$sql = "SELECT MIN(date) FROM suivi_client WHERE userid=".$utilisateur['userid'];
	$req = $DB_site2->query_first($sql);
	$date_select = intval(date("Y",$req[0]));
	$sql = "SELECT MIN(date) FROM mail_inbox WHERE userid=".$utilisateur['userid'];
	$req = $DB_site2->query_first($sql);
	if($date_select < intval(date("Y",$req[0]))) $date_select = intval(date("Y",$req[0]));
	$req = $DB_site2->query_first($sql);
	$sql = "SELECT MIN(date) FROM mail_outbox WHERE userid=".$utilisateur['userid'];
	$req = $DB_site2->query_first($sql);
	$req = $DB_site2->query_first($sql);
	if($date_select < intval(date("Y",$req[0]))) $date_select = intval(date("Y",$req[0]));
	$req = $DB_site2->query_first($sql);

	$today = intval(date("Y"));
	for ($i=$today; $i > ($date_select-1) ; $i--) { 
		$options .="<option value=\"$i\">$i</option>";
	}


	print "<div class=\"clear\"></div></div>
	  <div class=\"tab_special_client\" id=\"suivi_client\">
		<div style=\"text-align: center;font-weight:bold;\">  
		  <h2 style=\"width:100% !important\">SUIVI CLIENT</h2> <select id=\"annee_suivi\" onchange=\"load_by_year(".$utilisateur['userid'].",'all',this.value)\">".$options."</select>
		  <div id=\"sc_tous\">Tous</div><div id=\"sc_pjt\">Projet</div><div id=\"sc_int\">Compte</div><div id=\"sc_cde\">CDE</div><div id=\"sc_sav\">SAV</div><div id=\"sc_appel\">Appels</div><div id=\"sc_tri\">A trier</div>
		  <div id=\"sc_info\"><img src=\"../configurations/".$host."/images/templates/icone_info.gif\"></div>
          <div id=\"info_sc_email\">
              <p>A quoi correspondent les onglets ?</p>
              <p><strong>Tous :</strong> tous les mails et suivis internes correspondant à cette commande.</p>
              <p><strong>Projet :</strong> les mails concernant les projets du client (entrant et sortant) qui on étés triés avec le code #PJTxxxxx-yyyyy# xxxxx est le numéro de projet et yyyyy est le numero de client. Les mails concernant un projet dont le client fait parti mais n'en est pas le client pricipal.</p>
              <p><strong>Compte :</strong> les informations concernant le compte du client.</p>
              <p><strong>CDE :</strong> les mails concernant les commandes (<strong>entrant seulement</strong>) qui ont étés triés en tant que mails de commande avec le code #CDExxxxxx# (xxxxx est le numero de commande) pour ce client.</p>
              <p><strong>SAV :</strong> les mails concernant les SAV des commandes du client (entrant et sortant) qui on étés triés avec le code #SAVyyyyyy-xxxx# (yyyyyy est le numero de commande et xxxx est le numéro du sav).</p>
              <p><strong>A trier : </strong> les mails (entrant et sortant) concernant ce client qui ne sont pas triés, c'est à dire qu'ils ne sont attribués à rien, ni à une commande, ni un projet, ni un SAV</p>

          </div>
		</div>
		<div class=\"clear\"></div>";
	
	$tri_defaut = array(1 => 'sc_pjt', 2 => 'sc_cde', 3 => 'sc_sav', 4 => 'sc_pjt', 5 => 'sc_tous', 6 => 'sc_tous', 7 => 'sc_tous');
	$libtri = array('sc_tous' => 'all', 'sc_pjt' => 'pjt','sc_sav' => 'sav','sc_adv' => 'cde');

	//on affiche pas l'onglet projet par defaut pour les commerciaux si il n'y a pas de projets
	if($nb_projet_client1[0] == 0 && $nb_projet_client2[0] == 0 && ($user_info['service'] == 1 || $user_info['service'] == 4) ){
		$defaut = "sc_tous";
	}else{
		$defaut = $tri_defaut[$user_info['service']];
	}

	echo $filtreprojet;
	echo "<div id=\"tab_suivi_client\"></div>";
    ?>
	<script>
		$jq(document).ready(function () {
			var ladate = $jq('#annee_suivi').val();
			$jq("div").find('.filtreprojet').show();
			$jq('#<?php echo $defaut; ?>').addClass('suivi_onglet_hover_clt');
			loadsuivi('<?php echo $utilisateur['userid']; ?>','<?php echo $libtri[$defaut]; ?>',0,'undefined', '<?php echo $today; ?>');
            $jq('#sc_tous').click(function(){
            	$jq("div").find('.filtreprojet').show();
                $jq("div").find('.suivi_onglet_hover_clt').removeClass('suivi_onglet_hover_clt');
                $jq('#sc_tous').addClass('suivi_onglet_hover_clt');
                
                loadsuivi('<?php echo $utilisateur['userid']; ?>','all',0,undefined);
            });
            $jq('#sc_int').click(function(){
            	$jq("div").find('.filtreprojet').hide();
                $jq("div").find('.suivi_onglet_hover_clt').removeClass('suivi_onglet_hover_clt');
                $jq('#sc_int').addClass('suivi_onglet_hover_clt');

                loadsuivi('<?php echo $utilisateur['userid']; ?>','int',0,undefined);
            });
            $jq('#sc_tri').click(function(){
            	$jq("div").find('.filtreprojet').hide();
                $jq("div").find('.suivi_onglet_hover_clt').removeClass('suivi_onglet_hover_clt');
                $jq('#sc_tri').addClass('suivi_onglet_hover_clt');

                loadsuivi('<?php echo $utilisateur['userid']; ?>','tri',0,undefined);
            });
            $jq('#sc_pjt').click(function(){
            	$jq("div").find('.filtreprojet').show();
                $jq("div").find('.suivi_onglet_hover_clt').removeClass('suivi_onglet_hover_clt');
                $jq('#sc_pjt').addClass('suivi_onglet_hover_clt');
 
                loadsuivi('<?php echo $utilisateur['userid']; ?>','pjt',0,undefined);
            });
            $jq('#sc_cde').click(function(){
            	$jq("div").find('.filtreprojet').show();
                $jq("div").find('.suivi_onglet_hover_clt').removeClass('suivi_onglet_hover_clt');
                $jq('#sc_cde').addClass('suivi_onglet_hover_clt');

                loadsuivi('<?php echo $utilisateur['userid']; ?>','cde',0,undefined);
            });
            $jq('#sc_sav').click(function(){
            	$jq("div").find('.filtreprojet').show();
                $jq("div").find('.suivi_onglet_hover_clt').removeClass('suivi_onglet_hover_clt');
                $jq('#sc_sav').addClass('suivi_onglet_hover_clt');

                loadsuivi('<?php echo $utilisateur['userid']; ?>','sav',0,undefined);
            });
            $jq('#sc_appel').click(function(){
            	$jq("div").find('.filtreprojet').show();
                $jq("div").find('.suivi_onglet_hover_clt').removeClass('suivi_onglet_hover_clt');
                $jq('#sc_appel').addClass('suivi_onglet_hover_clt');

                loadsuivi('<?php echo $utilisateur['userid']; ?>','appel',0,undefined);
            });
		});

		function load_by_year(userid,request,date){
			$jq("div").find('.suivi_onglet_hover_clt').removeClass('suivi_onglet_hover_clt');
            $jq('#sc_tous').addClass('suivi_onglet_hover_clt');
			loadsuivi(userid,'all','all',undefined,date);
		}

	</script>
