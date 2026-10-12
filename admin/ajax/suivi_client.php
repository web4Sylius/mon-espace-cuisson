<?php
require_once($_SERVER['DOCUMENT_ROOT']."/admin/admin_global.php");
include_once($_SERVER['DOCUMENT_ROOT']."/class/projet.class.php");

//$limitdate=new datetime("1 year ago");

$prems=$_GET['prems'];

if($prems!=1){
	$prems=0;
}

if(in_array($user_info['username'],$adminSU))
  $authDelSuivi = 1;
else
  $authDelSuivi = 0;

?>

<script type="text/javascript">
function verif_delier(a){
    $jq(a).parent().find('.choix_delier').not('.modif_lier').toggle();
}

function mouseOver(id) {
    document.getElementById(id).style.display = "block";
}

function mouseOut(id) {
    document.getElementById(id).style.display = "none";
}
function resizeImgSuivi(){	
	var suivis = document.getElementsByClassName("suivitxt");
		for(var i = 0;i<suivis.length; i++){
			var images = suivis[i].getElementsByTagName("img");
			for(var y = 0; y<images.length; y++){
				if(images[y].getAttribute("width")>300 && images[y].getAttribute("height")>150){
					images[y].setAttribute("width","300");
					images[y].setAttribute("height", "150");
				}
			}
		}
}

function delier(suivi,projetid,type='suivi_client'){
    if (confirm("Voulez-vous délier de ce projet ?") == true) {
        if(type==null || type==''){
            var type = 'suivi_client';
        }
        $jq.ajax('ajax/suivi_client_liaison_projet.php?action=delete&table='+type+'&suiviid='+suivi+'&projetid='+projetid, {            
            success: function (data, status, xhr) {    // success callback function
                window.location = window.location.href;
            },
            error: function (jqXhr, textStatus, errorMessage) { // error callback 
                $jq('#btn_refresh_suivi_client').text('Erreur !' + errorMessage);
            },
            beforeSend: function() {
                $jq('#suivi_client table').remove();
                $jq('#suivi_client_append_after').show();
                $jq('#btn_refresh_suivi_client').text('Chargement en cours ...');
            }
        });
    }
}

function change_projet(div){
    $jq(div).parent().find('.modif_lier').toggle();
}

function change_projet_action(projet,suivi,type='suivi_client'){
    if(type==null || type==''){
        var type = 'suivi_client';
    }
    var projetid = $jq(projet).parent().find('option:selected').val()
    $jq.ajax('ajax/suivi_client_liaison_projet.php?action=update&table='+type+'&suiviid='+suivi+'&projetid='+projetid,   // request url
    {            
        success: function (data, status, xhr) {    // success callback function
            window.location = window.location.href;
        },
        error: function (jqXhr, textStatus, errorMessage) { // error callback 
            $jq('#btn_refresh_suivi_client').text('Erreur !' + errorMessage);
            console.log(errorMessage);
        },
        beforeSend: function() {
            $jq('#suivi_client table').remove();
            $jq('#suivi_client_append_after').show();
            $jq('#btn_refresh_suivi_client').text('Chargement en cours ...');
        }
    });
}
</script>
<?php
$suiviicon = array(
	"tel_out" => "<img src=\"images/tel_out.png\"><br>",
	"tel_in"  => "<img src=\"images/tel_in.png\"><br>",
	"mail_in" => "<img src=\"images/get_mail.png\"><br>"
);
$userid = htmlspecialchars($_GET['userid']);
$nb_cde_client = $DB_site2->query_first("SELECT COUNT(*) FROM facture WHERE userid='".$userid."' AND valide IN(1,5,7,8)");
$nb_projet_client1=$DB_site2->query_first("SELECT COUNT(*) FROM projet WHERE userid='".$userid."' ");
$nb_projet_client2=$DB_site2->query_first("SELECT COUNT(*) FROM projet AS p INNER JOIN projet_relation AS pr USING(projetid) WHERE pr.userid='".$userid."' ");
$nb_projet_client=$nb_projet_client1[0]+$nb_projet_client2[0];

// $nb_projet_client=$DB_site2->query_first("SELECT COUNT(*) FROM projet WHERE userid='".$userid."' ");

$localmail=array();
$req=$DB_site2->query("SELECT nom_contact,mail_contactFR, mail_contactEN FROM parametresgeneraux WHERE 1 ORDER BY parametre_id");
while($user=$DB_site2->fetch_array($req)){
    if($user['mail_contactFR']!="") $localmail[$user['mail_contactFR']]=$user['nom_contact'];
    if($user['mail_contactEN']!="") $localmail[$user['mail_contactEN']]=$user['nom_contact'];
}

function btn_suivi_cde_a_delier($userid,$suivi){
	$txt_retour="<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\" style=\"text-align: center; margin-bottom: 10px;\">"
                . "<tr>"
                    . "<td style=\"font-weight: bold;\">
                        cmde :
                        </td>
                    </tr>
                    <tr>
                        <td style=\"background-color:#EFEFEF;\">
                            ".$suivi['facturid']."
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <a id=\"suivi_cde_".$suivi['id']."\" class=\"link_btn\">Délier de cette cmde</a>
                        </td>
                    </tr>
            </table>";
    $txt_retour.="<script>
            \$jq(document).ready(function () {
                \$jq('#suivi_cde_".$suivi['id']."').click(function(){
                    \$jq.ajax('ajax/suivi_client_liaison_projet.php?action=delete&table=suivi_client&suiviid=".$suivi['id']."&facturid=".$suivi['facturid']."',   // request url
                    {            
                        success: function (data, status, xhr) {    // success callback function
                            loadsuivi('".$userid."', '".$_GET['request']."','1');
                        },
                        error: function (jqXhr, textStatus, errorMessage) { // error callback 
                            \$jq('#btn_refresh_suivi_client').text('Erreur !' + errorMessage);
                        },
                        beforeSend: function() {
                            \$jq('#suivi_client table').remove();
                            \$jq('#suivi_client_append_after').show();
                            \$jq('#btn_refresh_suivi_client').text('Chargement en cours ...');
                        }
                    });
                    
                });
            });
        </script>";
    return $txt_retour;    
}

function btn_suivi_cde_a_lier($DB,$userid,$suivi){
	$txt_retour ="";
	$liste_cde = $DB->query("SELECT facturid FROM facture WHERE userid='$userid'");
	while($cde = $DB->fetch_array($liste_cde)){
		$cde_txt .="<option value=\"".$cde['facturid']."\">".$cde['facturid']."</option>";
	}
	$txt_retour .= "<a id=\"suivi_cde_".$suivi['id']."\" class=\"link_btn link_btn_suivi\">Lier à une cmde</a>
	<div id=\"liaison_cde_".$suivi['id']."\" style=\"display:none;\">
    <form id=\"form_".$suivi['id']."\">
        <select name=\"facturid\" id=\"select_cde_".$suivi['id']."\">
            ".$cde_txt."
        </select>
        <input type=\"button\" value=\"OK\" id=\"btn_associer_cde_".$suivi['id']."\" class=\"link_btn\">
    </form>
	</div>";
	$txt_retour .= "<script>\$jq(document).ready(function () {
				\$jq('#suivi_cde_".$suivi['id']."').click(function(){
            		\$jq('#liaison_cde_".$suivi['id']."').toggle();
        		});
        		\$jq('#btn_associer_cde_".$suivi['id']."').click(function(){
                    var facturid=\$jq('#select_cde_".$suivi['id']." :selected').val();
                    \$jq.ajax('ajax/suivi_client_liaison_projet.php?action=assoc&table=suivi_client&suiviid=".$suivi['id']."&facturid='+facturid,   // request url
                    {            
                        success: function (data, status, xhr) {    // success callback function
                            loadsuivi('".$userid."', '".$_GET['request']."','1');
                        },
                        error: function (jqXhr, textStatus, errorMessage) { // error callback 
                            \$jq('#btn_refresh_suivi_client').text('Erreur !' + errorMessage);
                        },
                        beforeSend: function() {
                            \$jq('#suivi_client table').remove();
                            \$jq('#suivi_client_append_after').show();
                            \$jq('#btn_refresh_suivi_client').text('Chargement en cours ...');
                        }
                    });
                });
        		});
        	</script>";
	return $txt_retour;

}

function formatmail($mail,$sens,$readonly=false,$userid,$type_suivi){    
    
    global $DB_site2;
    global $linkcolor;
    global $authDelSuivi;
	global $nb_projet_client;
	global $localmail;
    global $host;

	$type    = "int";
	$contact = array();
	$td=array();

	if($mail['savid'] != 0){
        $type='sav';
    }elseif ($mail['projetid'] != 0) {
        $type='projet';
    }elseif ($mail['fournisseur'] != '') {
        $type='pro';
    }elseif ($mail['userid'] != 0 ) {
        $type='client';
    }

    foreach(array("from","to") as $mailtype){
        if(!isset($mail[$mailtype])) continue;
        $mails = preg_split("[,|;]",$mail[$mailtype]);
        $td[$mailtype]="";
        $virg="";
        foreach($mails as $mailaddr){
            
            if(isset($localmail[$mailaddr])){
                $td[$mailtype] .= $virg
                . "<div>".$localmail[$mailaddr]
                ."<span".($readonly?"":" class=\"suivilink\" onclick=\"close_choix_suivi(this);mail_auto(undefined,'".$mailaddr."');mail_services('$user[langue]','$user[site_code]','adv,commercial');\"").">".$mailaddr."</span>"
                ."</div>";
            }else{
                $sql = "SELECT userid,mail,telephone,portable,nom,prenom,raisonsociale AS societe";
                $sql .= " FROM utilisateur";
                $sql .= " WHERE mail <> ''";
                $sql .= " AND mail = '".addslashes($mailaddr)."'";
                $user = $DB_site2->query_first($sql);

                $usercontact=$DB_site2->query_first("SELECT * FROM utilisateur_assoc WHERE utilisateur_assoc_email='".mysqli_real_escape_string($DB_site2->link_id,$mailaddr)."'");
                
                $sql = "SELECT *";
                $sql .= " FROM fournisseur";
                $sql .= " WHERE mail <> ''";
                $sql .= " AND mail = '".addslashes($mailaddr)."'";
                $contact = $DB_site2->query_first($sql);

                if($mailaddr == $user['mail']){

                    $td[$mailtype] .= $virg
                    . "<div>".$user['societe']." ".$user['prenom']." ".$user['nom']
                    . "<span".($readonly?"":" class=\"suivilink mail_out\" onclick=\"close_choix_suivi(this);mail_auto(undefined,'".$user['mail']."');mail_services('$user[langue]','$user[site_code]','adv,commercial');\"").">".$user['mail']."</span>"
                    . "<span".($readonly?"":" class=\"suivilink tel_out\" onclick=\"close_choix_suivi(this);suiviauto('tel_out','client',".$user['userid'].",'Appel téléphone ".$user['societe']." (".$user['nom']." ".$user['prenom'].")',".($usertel?"'$user[telephone]'":"undefined").");self.location.hash='#suivitop';\"").">".$user['telephone']."</span>";

                    if($user['portable'] != "") $td[$mailtype] .= "<span".($readonly?"":" class=\"suivilink tel_out\" onclick=\"close_choix_suivi(this);suiviauto('tel_out','client',".$user['userid'].",'Appel téléphone ".$user['societe']." (".$user['nom']." ".$user['prenom'].")',".($usertel?"'$user[portable]'":"undefined").");self.location.hash='#suivitop';\"").">".$user['portable']."</span>";

                    $td[$mailtype] .= "</div>";

                } elseif($mailaddr == $contact['mail']){
                    $td[$mailtype] .= $virg
                    . "<div>".$contact['societe']." ".$contact['prenom']." ".$contact['nom']
                    . "<span".($readonly?"":" class=\"suivilink mail_out\" onclick=\"close_choix_suivi(this);mail_auto(undefined,'".$contact['mail']."');mail_suivi('suivi_commande','".$mail['suiviid']."')\"").">".$contact['mail']."</span>"
                    . "<span".($readonly?"":" class=\"suivilin tel_out\" onclick=\"close_choix_suivi(this);suiviauto('tel_out','pro',".$contact['id'].",'Appel téléphone ".$contact['societe']." (".$contact['nom']." ".$contact['prenom'].",".($usertel?"'$contact[telephone]'":"undefined").")');self.location.hash='#suivitop';\"").">".$contact['telephone']."</span>";

                    if($contact['portable'] != "") $td[$mailtype].="<span".($readonly?"":" class=\"suivilink tel_out\" onclick=\"close_choix_suivi(this);suiviauto('tel_out','pro',".$contact['id'].",'Appel téléphone ".$contact['societe']." (".$contact['nom']." ".$contact['prenom'].")',".($usertel?"'$contact[portable]'":"undefined").");self.location.hash='#suivitop';\"").">".$contact['portable']."</span>";

                    $td[$mailtype] .= "</div>";

                } elseif($mailaddr==$usercontact['utilisateur_assoc_email']) {
                    $user=$DB_site2->query_first("SELECT * FROM utilisateur WHERE userid=".$usercontact['userid']);
                    $td[$mailtype] .= $virg
                    . "<div>".$usercontact['utilisateur_assoc_prenom']." ".$usercontact['utilisateur_assoc_nom']
                    . "<span".($readonly?"":" class=\"suivilink mail_out\" onclick=\"close_choix_suivi(this);mail_auto(undefined,'".$usercontact['utilisateur_assoc_email']."');mail_services('$user[langue]','$user[site_code]','adv,commercial');\"").">".$usercontact['utilisateur_assoc_email']."</span>"
                    . "<span".($readonly?"":" class=\"suivilink tel_out\" onclick=\"close_choix_suivi(this);suiviauto('tel_out','client',".$user['userid'].",'Appel téléphone ".$user['societe']." (".$usercontact['utilisateur_assoc_nom']." ".$usercontact['utilisateur_assoc_prenom'].")',".($usertel?"'$usercontact[utilisateur_assoc_tel]'":"undefined").");self.location.hash='#suivitop';\"").">".$usercontact['utilisateur_assoc_tel']."</span>
                    </div>";
                    
                } else {

                    $td[$mailtype] .= $virg."<div>".$mailaddr."</div>";

                }
            }
            
            $virg=", ";
        }
    }

    $pjlist = ""; 


    $sql = "SELECT *";
    $sql .= " FROM mail_attachment";
    $sql .= " WHERE mailid = ".$mail['id'];
    $reqpj = $DB_site2->query($sql);
    $i = 0;
    if($DB_site2->num_rows($reqpj) > 0){

        while($pj = $DB_site2->fetch_array($reqpj)){
        	$year = date("Y", $mail['date']);
            $valeur = ROOT_PATH."configurations/".$host."/mails/attachments/$year/".$pj['id'].".".$pj['ext'];
            if (file_exists($valeur)){
                $pjlist .= "<form method=\"post\" style=\"margin: 0px;\" align=\"center\" type=\"button\" action=\"/admin/includes/download.php\">
            <input type=\"submit\" value=\" \" onmouseover=\"mouseOver('special".$mail['id'].$i."')\" onmouseout=\"mouseOut('special".$mail['id'].$i."')\" class=\"dlbutton\"/>
            <input type=\"hidden\" name=\"page\" value=\"".$valeur."\"/>
            </form> <div id=\"special".$mail['id'].$i."\" class=\"spehover\">".$pj['name']."</div>";
            }
            $i++;
        }
    }

    $sens == "in" ? $icon = "get_mail.png" : $icon = "send_mail.png";

    $content_div = ""
		. "<div class=\"transfer\" onclick=\"reply(".$mail['id'].",'".$sens."box');\" title=\"Transférer\"></div>"
		. "<div class=\"reply\" onclick=\"reply(".$mail['id'].",'".$sens."box',1);\" title=\"Répondre\"></div>"
		. "<div class=\"clear\"></div>";
	

		
		$suivi_assoc="<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\" style=\"text-align: center; margin-bottom: 10px;\">";
		//$duplication_count=$DB_site2->query_first("SELECT COUNT(*) FROM suivi_duplication WHERE suivi_origine_id = ".$mail['id']." AND suivi_origine_type = 'client'");


		$suivi_assoc .= "<tr>"
						. "<td style=\"font-weight: bold;\">
							<input type=\"button\" name=\"transfert_suivi\" class=\"link_btn\" value=\"Dupliquer\" onclick=\"suivi_copy('cmd', ".$userid.", ".$mail['id'].", '".$type_suivi."');\">
						   </td>"
					. "</tr>";

		if($mail['facturid'] != 0){
			
			$suivi_assoc .= "<tr>"
							. "<td style=\"font-weight: bold;\">
									Commande :<br>
								   </td>"
							. "</tr>";
			$suivi_assoc .= "<tr>"
							. "<td style=\"background-color: #EFEFEF;\"><a href=\"/admin/facture.php?action=editer&facturid=".$mail['facturid']."\" target=\"_blank\">".$mail['facturid']."</a></td>"
						. "</tr>"
						."<tr>
						   <td>
								<a id=\"suivi_cmd_".$mail['id']."\" class=\"delier_btn\" onclick=\"delierMail(".intval($userid).",'".$mail['facturid']."', ".$mail['id'].", '".$sens."', 'commande');\">Délier de cette cmd</a>
						   </td>
						</tr>";

		}else{
			$suivi_assoc .= "<tr>"
						. "<td style=\"font-weight: bold;\">
							<input type=\"button\" name=\"transfert_suivi\" class=\"link_btn\" value=\"Lier à une commande\" onclick=\"CheckListTransferts('cmd', ".$userid.", ".$mail['id'].", '".$sens."');\">
						   </td>"
					. "</tr>";
		}			
					
		//lier a sav
		if($mail['savid'] != 0 ){
			$suivi_assoc .= "<tr>"
								. "<td style=\"font-weight: bold;\">
									SAV :<br>
								   </td>"
							. "</tr>";
			$suivi_assoc .= "<tr>"
							. "<td style=\"background-color: #EFEFEF;\"><a href=\"/admin/facture.php?action=editer&facturid=".$mail['savid']."\" target=\"_blank\">".$mail['savid']."</a></td>"
						. "</tr>"
						."<tr>
						   <td>
								<a id=\"suivi_sav_".$mail['id']."\" class=\"delier_btn\" onclick=\"delierMail(".intval($userid).",'".$mail['savid']."', ".$mail['id'].", '".$sens."', 'sav');\">Délier de ce sav</a>
						   </td>
						</tr>";				

		}else{
			$suivi_assoc .= "<tr>"
						. "<td style=\"font-weight: bold;\">
							<input type=\"button\" name=\"transfert_suivi\" class=\"link_btn\" value=\"Lier à un sav\" onclick=\"CheckListTransferts2('sav', ".$userid.", ".$mail['id'].",'classique','scli');\">
							<div id=\"lier_sav_scli_".$mail['id']."\" style=\"display:none;\">
								<select id=\"select_lier_sav_scli_".$mail['id']."\" onchange=\"lierMail(".intval($userid).",this.value, ".$mail['id'].", '".$sens."', 'sav')\"></select>
							</div>	
						   </td>"
					. "</tr>";	
		}			
			
		$cpt=0;
		$sql = "SELECT * FROM suivi_duplication WHERE suivi_origine_id = ".$mail['id']." AND suivi_origine_type = 'client' AND suivi_copie_type='cli' ORDER BY suivi_copie_type, copie_id";
		$req = $DB_site2->query($sql);
		while($row = $DB_site2->fetch_array($req)){
			$cpt++;
			if($cpt==1){
				$suivi_assoc .= "<tr>"
								. "<td style=\"font-weight: bold;\">
									Client(s) :<br>
								   </td>"
							. "</tr>";
			}
			$suivi_assoc .=  "<tr>"
							. "<td style=\"background-color: ".($cpt%2 == 0 ? '#E0E0E0' : '#EFEFEF').";\"><a href=\"/utilisateur.php?action=editer&userid=".$row['copie_id']."\" target=\"_blank\">".$row['copie_id']."</a></td>"
						. "</tr>";
		}
		$suivi_assoc .= "<tr>"
						. "<td style=\"font-weight: bold;\">
							<input type=\"button\" name=\"transfert_suivi\" class=\"link_btn\" value=\"Lier à un client\" onclick=\"document.getElementById('suivi_recherche_".$mail['id']."').style.display = 'block';\">
							<input type=\"texte\" id=\"suivi_recherche_".$mail['id']."\" name=\"transfert_suivi_recherche\" style=\"margin: 5px auto; width: 100px; display: none;\" onkeyup=\"checkListClients(this.value, ".$userid.", ".$mail['id'].", '".$type_suivi."');\">
						   </td>"
					. "</tr>";
		
		$suivi_assoc .= "</table>";

	
	$valeur_pj = explode(',', $mail['pj']);
	$aff = "";
	$i = 0;
	foreach ($valeur_pj as $key)
	{
		if ($key!='')
		{
			$valeur = ROOT_PATH."images/mail/pj2/".$key;
			if (file_exists($valeur))
			{

				$aff .= "<form method=\"post\" style=\"margin: 0px;\" align=\"center\" type=\"button\" action=\"/admin/includes/download.php\">
	 	              <input type=\"submit\" value=\" \" onmouseover=\"mouseOver('file_".$i."')\" onmouseout=\"mouseOut('file_".$i."')\" class=\"dlbutton\"/>
	                <input type=\"hidden\" name=\"page\" value=\"".$valeur."\"/>
	              </form> <div id=\"file_".$i."\" class=\"spehover\">".$key."</div>";
	         }
         }
        $i ++;
	}
	
	
	
	
	if($mail['projetid']!=0){
        $liste_projets=$DB_site2->query("   SELECT * FROM (
                (SELECT p.* FROM projet AS p WHERE userid='".$userid."' AND p.projetid != '".$mail['projetid']."')
                    UNION
                    (SELECT p.* FROM projet AS p INNER JOIN projet_relation AS pr USING(projetid) WHERE pr.userid='".$userid."' AND p.projetid != '".$mail['projetid']."')
                ) AS T ORDER BY T.libelle ASC ");
        $projet_txt="";
        while($liste_projet=$DB_site2->fetch_array($liste_projets)){
            $projet_txt.="<option value=\"".$liste_projet['projetid']."\">PJT".$liste_projet['projetid']. " - ".substr($liste_projet['libelle'],0,40)."</option>";
        }
		$projet=$DB_site2->query_first("SELECT * FROM projet WHERE projetid='".$mail['projetid']."' ");
		$projettxt="<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\" style=\"text-align: center; margin-bottom: 10px;\">"
					. "<tr>"
						. "<td style=\"font-weight: bold;\">
							Projet :
						   </td>
						</tr>
						<tr>
							<td style=\"background-color:#EFEFEF;\">
								".$projet['libelle']."
							</td>
						</tr>
						<tr>
						   <td>
								<a onclick=\"verif_delier(this)\" class=\"link_btn\">Délier de ce projet</a>
                                    <div class=\"choix_delier\">
                                        <div onclick=\"change_projet(this)\" class=\"link_btn_delier\">Changer de projet</div>
                                        <div class=\"modif_lier\">
                                            <select name=\"projetid\">
                                                ".$projet_txt."
                                            </select>
                                            <input type=\"submit\" onclick=\"change_projet_action(this,".$mail['id'].",'".$type_suivi."')\" value=\"changer\"/>
                                        </div>
                                        <div onclick=\"delier(".$mail['id'].",".$mail['projetid'].",'".$type_suivi."')\" class=\"link_btn_delier\">Délier de ce projet</div>
                                    </div>
						   </td>
						</tr>
				</table>";
		$classprojet=" projet_".$mail['projetid'];
	} else {
		$projettxt="";
		$classprojet=" ssprojet";
	}
	

	
    $affMail = ""
      . "<tr id=\"".$type."-".$mail['id']."-".$mail['userid']."\" class=\"suivi$type".$classprojet."\" style=\"border-bottom: 1px solid #000;\"><script type=\"text/javascript\">resizeImgSuivi();</script>"
	    . "<td><img src=\"images/$icon\"><br>".$mail['utilisateur']."<br>".date("d/m/Y",$mail['date'])."<br>".date("H:i",$mail['date'])."</td>"
	    . "<td style=\"text-align: center;\">"
			. $suivi_assoc
			. ""
			. "<div style=\"position: relative;\">"
			. "<div id=\"type_suivi_resultats_".$mail['id']."\" class=\"type_suivi_resultats\"></div>"
			. "</div>"
			. "<div style=\"position: relative;\">"
			. "<div id=\"type_suivi_clients_".$mail['id']."\" class=\"type_suivi_resultats\"></div>"
			. "</div>";
    
	
	if($mail['projetid']==0){
		if($nb_projet_client>0){
			$projet_txt="";
			// $liste_projets=$DB_site2->query("SELECT * FROM projet WHERE userid='".$userid."' ORDER BY libelle ASC ");
			$liste_projets=$DB_site2->query(" 	SELECT * FROM (
										(SELECT p.* FROM projet AS p WHERE userid='".$userid."' )
										UNION
										(SELECT p.* FROM projet AS p INNER JOIN projet_relation AS pr USING(projetid) WHERE pr.userid='".$userid."' )
									) AS T ORDER BY T.libelle ASC ");
			while($liste_projet=$DB_site2->fetch_array($liste_projets)){
				$projet_txt.="<option value=\"".$liste_projet['projetid']."\">".$liste_projet['libelle']."</option>";
			}

			$affMail .= "<a id=\"suivi_projet_".$mail['id']."\" onclick=\"affichePjt(".$mail['id'].");\" class=\"link_btn\">Lier à un projet</a><br>
							<div id=\"liaison_projet_".$mail['id']."\" style=\"display:none;\">
								<form id=\"form_".$mail['id']."\">
									<select name=\"projetid\" id=\"select_mail_".$mail['id']."\">
										".$projet_txt."
									</select>
									<input type=\"button\" value=\"OK\" id=\"btn_associer_".$mail['id']."\" class=\"link_btn\">
								</form>
							</div>
							<script>
								\$jq(document).ready(function () {
									\$jq('#btn_associer_".$mail['id']."').click(function(){
										var projetid=\$jq('#select_mail_".$mail['id']." :selected').val();
										\$jq.ajax('ajax/suivi_client_liaison_projet.php?action=assoc&table=".$type_suivi."&suiviid=".$mail['id']."&projetid='+projetid,   // request url
                                        {
											success: function (data, status, xhr) {    // success callback function
                                                 loadsuivi('".$userid."', '".$_GET['request']."','1');
											},
											error: function (jqXhr, textStatus, errorMessage) { // error callback 
												\$jq('#btn_refresh_suivi_client').text('Erreur !' + errorMessage);
											},
											beforeSend: function() {
												\$jq('#suivi_client table').remove();
												\$jq('#suivi_client_append_after').show();
												\$jq('#btn_refresh_suivi_client').text('Chargement en cours ...');
											}
										});
									});
								});
							</script>";
		}
	} else {
		$affMail .= $projettxt."
						<script>
                        

						</script>";
    }
	
    // TSI gestion de l'affichage suppression d'un suivi / mail pour SU et utilisateur_admin
    if ($authDelSuivi==1){
        //$affMail .= "<a href=\"delSuivi.php?userid=".$userid."&suividi=&mailid=".$mail['id']."\"><img src=\"images/supprimer.gif\" onclick=\"delSuivi.php?userid=".$userid."&mailid=".$mail['id']."\" width=\"20\"></a>";
        $affMail .= "<a href=\"\"><img src=\"images/supprimer.gif\" onclick=\"cleanSuiviMail(".$userid.",".$mail['id'].",'$sens')\" width=\"20\"></a>";
	}
	$affMail .= "</td>";
	
	
	$affMail .= "<td class=\"suivitxt\">";
	    if(isset($td['from']) && !empty($td['from']))
            $affMail .="<div class=\"addr\">De : ".$td['from']."</div>";
	    
	$affMail .="<div class=\"addr\">Pour : ".$td['to']."</div>"
	    . "<div class=\"subject\">Sujet : <b>".$mail['subject'].($readonly ? "" : $content_div)."</b></div>"
	    . formatsuivi($mail['body'])
	    //. ($pj != "" ? "<br>PJ : ".str_replace(","," | ",$pjlist['pj']):"")
        . "</td>"
	    . "<td>".$pjlist //.$aff.$pjlist.
        . "</td>"
	    . "<td></td>";
		
		
		
    $affMail .= "</tr>";
    
    return $affMail;
}

function cause($case){
	switch($case){
		case 0:
			$etat .= "<br>(cause prix)";
			break;
		case 1:
			$etat .= "<br>(cause délai)";
			break;
		case 2:
			$etat .= "<br>(cause projet abandonné)";
			break;
		case 3:
			$etat .= "<br>(trop relancé)";
			break;
		case 4:
			$etat .= "<br>(mauvais rapports commerciaux)";
			break;
		case 5:
			$etat .= "<br>(ne veux pas dire)";
			break;
		case 6:
			$etat .= "<br>(autre)";
			break;
		case 7:
			$etat .= "<br>(injoignable)";
			break;
		case 8:
			$etat .= "<br>(double compte)";
			break;
		case 9:
			$etat .= "<br>(petit dossier)";
			break;
		case 10:
			$etat .= "<br>(achat ailleurs)";
			break;
		case 13:
			$etat .= "<br>(sans conseils)";
		break;
    }
    return $etat;
}

function searchMail($userid=false,$facturid=null,$fournisseur=null,$savid=null,$projetid=null,$compta=null,$limit=false,$destinataire=null,$date=null){
	global $DB_site2 ;
	global $suivitd;
	//global $limitdate;
	$cde = false;
	
    $where="1";
	if($userid){
	
        if(!is_null($projetid)){
            if($projetid==="*"){
                $projets=new projets();
                $u_projets=$projets->utilisateur($userid);
                if(count($u_projets)>0)
                    $where.=" AND projetid IN (".implode(",",array_map(function($e){ return $e->projetid;},$u_projets)).")";
                else
                    $where.=" AND false";
            }elseif($projetid===0 || $projetid===""){
                $where.=" AND userid=".$userid;
            }
        }else{
            $projets=new projets();
            $u_projets=$projets->utilisateur($userid);
            $where.=" AND (userid = ".$userid;
            if(count($u_projets)>0)
                $where.=" OR projetid IN (".implode(",",array_map(function($e){ return $e->projetid;},$u_projets)).")";
            $where.=")";
        }
    }
    
    if(is_numeric($projetid)){
        $where.=" AND projetid=".intval($projetid);
    }
    
    if($destinataire!==false && !is_null($destinataire)){
        if($destinataire===""){
            $where.=" AND fournisseur!=''";
        }else{
            $listmail=array();
            $req=$DB_site2->query("SELECT userid,mail FROM utilisateur WHERE userid IN (".implode(",",array_map("intval",$destinataire)).")");
            while($u=$DB_site2->fetch_array($req)){
                $listmail[]=$u['mail'];
            }
            $sqlmails="'".implode("','",array_map(function($e){
                global $DB_site2;
                return mysqli_real_escape_string($DB_site2->link_id,$e);
            },$listmail))."'";
            $where.=" AND (`to` IN (".$sqlmails.") OR `from` IN (".$sqlmails."))";
        }
    }
    
	if(!is_null($facturid)){
        if($facturid==="*") {
            $where.=" AND facturid>0";
            $cde = true;
        }else{
            $where.=" AND facturid=".intval($facturid);
        }
    }
	if(!is_null($fournisseur)){
        if(is_array($fournisseur)){
            if(count($fournisseur)==0)
                $where.=" AND fournisseur=''";
            else
                $where.=" AND fournisseur IN ('".implode("','",array_map(function($e){
                    global $DB_site2;
                    return mysqli_real_escape_string($DB_site2->link_id,$e);
                },$fournisseur))."')";
        }else{
            $where.=" AND fournisseur='".mysqli_real_escape_string($DB_site2->link_id,$fournisseur)."'";
        }
    }
	
	if(!is_null($savid))
        if($savid==="*")
            $where.=" AND savid>0";
        else
            $where.=" AND savid=".intval($savid);

    if(!is_null($date)){
        $year = $date;
        $anneemin = mktime(0,0,0,1,1,$year);
        $anneemax = mktime(0,0,0,12,31,$year);
        $where.=" AND date > $anneemin AND date < $anneemax";
    }    
    
	/*if(!is_null($compta))
        $where.=" AND compta='".($compta?"oui":"non")."'";*/
	
	//if($limit) $where.=" AND date>".$limitdate->getTimestamp();
	//on ne veut pas les mails sortant concernant la commande dans le suivi client
	
    //on filtre les emails auto
    $where .=" AND auto=1";

    // if($limit) $where .=" LIMIT 5";
	$where.=" ORDER BY `date` DESC";
    // var_dump($where);
	// return;
    //if(!$cde){
	    $sql = "SELECT * FROM mail_outbox WHERE ".$where;
	    $requete = $DB_site2->query($sql);
	    while($mail = $DB_site2->fetch_array($requete)){
			$suivitd[$mail['date'].uniqid()] = formatmail($mail,"out",false,$userid,'mail_outbox');
			// echo "suivi - ".$mail['date']." => ".$mail['utilisateur']." !<br>";
	    }
	//}

    $sql = "SELECT * FROM mail_inbox WHERE ".$where;
    $requete = $DB_site2->query($sql);
    while($mail = $DB_site2->fetch_array($requete)){
		$suivitd[$mail['date'].uniqid()] = formatmail($mail,"in",false,$userid,'mail_inbox');
    }
}

function searchSuivi($userid=false,$int=false,$projetid=false,$limit=false,$cde=false,$appel=false,$fourn=false,$destinataire=false,$interne=false,$search=null,$date=null){
    global $DB_site2 ;
	global $suivitd;
	global $nb_projet_client;
	global $nb_cde_client;
    //global $limitdate;
    global $suiviicon;

	$sql = "SELECT * FROM suivi_client WHERE 1";
	if($userid){
        if($projetid){
            if($projetid=="*"){
                $projets=new projets();
                $u_projets=$projets->utilisateur($userid);
                if(count($u_projets)>0)
                    $sql.=" AND projetid IN (".implode(",",array_map(function($e){ return $e->projetid;},$u_projets)).")";
                else
                    $sql.=" AND false";
            }
        }else{
            $sql.=" AND (userid = ".intval($userid);
            $projets=new projets();
            $u_projets=$projets->utilisateur($userid);
            if(count($u_projets)>0){
                $sql.=" OR projetid IN (".implode(",",array_map(function($e){ return $e->projetid;},$u_projets)).")";
            }
            $sql.=")";
        }
     }
    if(is_numeric($projetid)){
        $sql.=" AND projetid=".$projetid;
    }
    
    $types=array();
    $dest=array();
    if($interne){
        $types[]="";
    }
    
    if(!is_null($fourn) && $fourn!==false){
        if($fourn===""){
            
        }else{
            $types[]="pro";
            $fid=array();
            $sqlf=is_array($fourn)?implode("','",array_map(function($e){
                global $DB_site2;
                return mysqli_real_escape_string($DB_site2->link_id,$e);
            },$fourn)):mysqli_real_escape_string($DB_site2->link_id,$fourn);
            $req=$DB_site2->query("SELECT id FROM fournisseur WHERE societe IN ('".$sqlf."')");
            while($f=$DB_site2->fetch_array($req)){
                $dest[]=$f['id'];
            }
        }
    }else
        $types[]="pro";

    if(!is_null($destinataire) && $destinataire!==false){
        if($destinataire==""){
            
        }else{
            $types[]="client";
            $types[]="assoc";
            $types[]="macave";
            if(!is_array($destinataire)) $destinataire=array($destinataire);
            $dest=array_merge($dest,array_map("intval",$destinataire));
        }
    }else{
        $types[]="client";
        $types[]="assoc";
        $types[]="macave";
    }
        
    if(count($types)>0) $sql.=" AND type IN ('".implode("','",$types)."')";
    if(count($dest)>0) $sql.=" AND destinataire IN ('','".implode("','",$dest)."')";
	//if($int) $sql.=" AND moyen='' AND suivi LIKE '%[infos_client]%' AND rappel=0";
    if($int) $sql.=" AND compte = 0 ";
	if($cde) $sql .=" AND facturid>0";
    if($appel) $sql .=" AND moyen IN ('tel_in','tel_out')";
    if(!is_null($search)) $sql.=" AND suivi LIKE '%".mysqli_real_escape_string($DB_site2->link_id,$search)."%'";
    if(!is_null($date)){
        $year = $date;
        $anneemin = mktime(0,0,0,1,1,$year);
        $anneemax = mktime(0,0,0,12,31,$year);
        $sql.=" AND date > $anneemin AND date < $anneemax";
    }
	//if($limit) $sql.=" AND date>".$limitdate->getTimestamp();
	$sql.=" ORDER BY date DESC"; 
    // if($limit) $sql.=" LIMIT 5";

    $requete = $DB_site2->query($sql);
    
    while($suivi = $DB_site2->fetch_array($requete)){
        $causeperte = "";
        /*Philippe insite pour que les modif fiche client n'ai aucun etat. Or c'est impossible vu comment c'est fait. On recupere le dernier etat pour y mettre le meme. Donc ici on enleve l'état juste pour l'affichage */
        if($suivi['suivi'] == strpos($suivi['suivi'], '[infos_client]')) $suivi['etat'] = -1;

        /* Check de l'etat*/
        switch($suivi['etat']){
            case -1:
                $etat = "<span></span>";
                break;
            case 0:
			case 6:
                $etat  = "<span style=\"color:red;\">Client perdu</span>";

                //Il suffit que le délai d'enregistrement dans "causeperte" ait pris 1 seconde de plus que dans suivi_client, la liaison ne se fait plus. Il faut donc rajouter une fourchette de 10 secondes pour tomber plus juste... (la liaison aurait du se faire sur l'id de causeperte...)
                $sql = "SELECT *";
                $sql .= " FROM causeperte";
                $sql .= " WHERE date >= " . ($suivi['date'] - 5); 
                $sql .= " AND date <= " . ($suivi['date'] + 5);
                $sql .= " AND userid = ".$suivi['userid'];
                $cause = $DB_site2->query_first($sql);
                
                if($cause['article'] != "" || $cause['marque'] != ""){
                    $causeperte .= "<br>Achat ".$cause['article']." ".$cause['marque'];
                    if($cause['prix'] != "") $causeperte .= " prix : ".$cause['prix'];
                }
                if($cause['lieu'] != ""){
                    if($causeperte == "") $causeperte = "<br>Achat";
                    $causeperte .= " chez ".$cause['lieu'];
                }

                $etat .= cause($cause['cause']);
                    
                break;

            case 1:
                $etat = "<span style=\"color:#0000AA;\">&Agrave; suivre</span>";
                break;
            case 2:
                $etat = "<span style=\"color:#0000FF;\">Attente info client</span>";
                break;
            case 3:
                $etat = "<span style=\"color:#000088;\">&Agrave; chiffrer</span>";
                break;
            case 4:
                $etat = "<span style=\"color:#000000;\">Dossier sous traité</span>";
                break;
            case 5:
                $etat = "<span style=\"color:#00AA00;\">En commande</span>";
                break;
			case 7:
                $etat = "<span style=\"color:#000088;\">Devis envoyé : suivi serv. Cial.</span>";
                break;
			case 8:
                $etat = "<span style=\"color:#000088;\">Devis envoyé : suivi Cial attrib.</span>";
                break;
			case 9:
                $etat = "<span style=\"color:#000088;\">Nouvel événement</span>";
                break;
        }

        $classtype = "int";
        $tddest    = "";

        if($suivi['type'] != ""){
            
            $classtype = $suivi['type'];

            switch($suivi['type']){
                case "pro":
                    $sql = "SELECT *";
                    $sql .= " FROM fournisseur";
                    $sql .= " WHERE id = ".$suivi['destinataire'];
                    $contact = $DB_site2->query_first($sql);
                    break;
                case "client":
                    $sql = "SELECT *, userid AS id";
                    $sql .= " FROM utilisateur";
                    $sql .= " WHERE userid = ".$suivi['destinataire'];
                    $contact = $DB_site2->query_first($sql);
                    break;
                case "assoc":
                    $sql = "SELECT utilisateur_assoc_prenom AS prenom, utilisateur_assoc_nom AS nom, utilisateur_assoc_tel AS telephone, utilisateur_assoc_portable AS portable, utilisateur_assoc_email AS mail, utilisateur_assoc_id AS id FROM utilisateur_assoc WHERE utilisateur_assoc_id = ".$suivi["destinataire"];
                    $contact = $DB_site2->query_first($sql);
                    $contact["langue"] = "FR";
                    break;    
            }

            $tddest = "<div class=\"addr\">Pour : 
            <div>"
            . $contact['societe']." ".$contact['prenom']." ".$contact['nom']
            . "<span onclick=\"suiviauto('mail_out','".$suivi['type']."',".$suivi['destinataire'].");mail_suivi('suivi_client',".$suivi['destinataire'].");\">".$contact['mail']."</span>"
            . "<span onclick=\"suiviauto('tel_out','".$suivi['type']."',".$contact['id'].",'Appel téléphone ".$contact['societe']." (".$contact['nom']." ".$contact['prenom'].")',".($usertel?"'$contact[telephone]'":"undefined").");\">".epigy($contact['telephone'],$contact['langue'])."</span>";

            if($contact['portable'] != "") $tddest .= "<span onclick=\"suiviauto('tel_out','".$suivi['type']."',".$contact['id'].",'Appel téléphone ".$contact['societe']." (".$contact['nom']." ".$contact['prenom'].",".($usertel?"'$contact[portable]'":"undefined").")');\">".epigy($contact['portable'],$contact['langue'])."</span>";
            $tddest.="</div>
            </div>";
        }
        
                
        $suivi_assoc="<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\" style=\"text-align: center; margin-bottom: 10px;\">";
        //$duplication_count=$DB_site2->query_first("SELECT COUNT(*) FROM suivi_duplication WHERE suivi_origine_id = ".$suivi['id']." AND suivi_origine_type = 'client'");

        $cpt=0;
        /*$sql = "SELECT * FROM suivi_duplication WHERE suivi_origine_id = ".$suivi['id']." AND suivi_origine_type = 'client' AND suivi_copie_type='cmd' ORDER BY suivi_copie_type, copie_id";
        $req = $DB_site2->query($sql);
        while($row = $DB_site2->fetch_array($req)){
            $cpt++;
            if($cpt==1){
                $suivi_assoc .= "<tr>"
                                . "<td style=\"font-weight: bold;\">
                                    Commande(s) :<br>
                                    </td>"
                            . "</tr>";
            }
            $suivi_assoc .= "<tr>"
                            . "<td style=\"background-color: ".($cpt%2 == 0 ? '#E0E0E0' : '#EFEFEF').";\"><a href=\"/admin/facture.php?action=editer&facturid=".$row['copie_id']."\" target=\"_blank\">".$row['copie_id']."</a></td>"
                        . "</tr>";
        }
        /*$suivi_assoc .= "<tr>"
                        . "<td style=\"font-weight: bold;\">
                            <input type=\"button\" name=\"transfert_suivi\" style=\"background-color: #AA1147; border: medium none; border-radius: 10px; color: white; font-size: 10px; font-weight: bold; padding: 1px 10px 3px; cursor: pointer; margin: 5px 0;\" value=\"Dupliquer\" onclick=\"suivi_copy('cmd', ".$userid.", ".$suivi['id'].",'classique');\">
                            </td>"
                    . "</tr>";

        $suivi_assoc .= "<tr>"
                        . "<td style=\"font-weight: bold;\">
                            <input type=\"button\" name=\"transfert_suivi\" style=\"background-color: #AA1147; border: medium none; border-radius: 10px; color: white; font-size: 10px; font-weight: bold; padding: 1px 10px 3px; cursor: pointer; margin: 5px 0;\" value=\"Lier à une commande\" onclick=\"CheckListTransferts('cmd', ".$userid.", ".$suivi['id'].",'classique');\">
                            </td>"
                    . "</tr>";		
        */
        
            
       /* $cpt=0;
        $sql = "SELECT * FROM suivi_duplication WHERE suivi_origine_id = ".$suivi['id']." AND suivi_origine_type = 'client' AND suivi_copie_type='cli' ORDER BY suivi_copie_type, copie_id";
        $req = $DB_site2->query($sql);
        while($row = $DB_site2->fetch_array($req)){
            $cpt++;
            if($cpt==1){
                $suivi_assoc .= "<tr>"
                                . "<td style=\"font-weight: bold;\">
                                    Client(s) :<br>
                                    </td>"
                            . "</tr>";
            }
            $suivi_assoc .=  "<tr>"
                            . "<td style=\"background-color: ".($cpt%2 == 0 ? '#E0E0E0' : '#EFEFEF').";\"><a href=\"/utilisateur.php?action=editer&userid=".$row['copie_id']."\" target=\"_blank\">".$row['copie_id']."</a></td>"
                        . "</tr>";
        }*/
        /*$suivi_assoc .= "<tr>"
                        . "<td style=\"font-weight: bold;\">
                            <input type=\"button\" name=\"transfert_suivi\" style=\"background-color: #AA1147; border: medium none; border-radius: 10px; color: white; font-size: 10px; font-weight: bold; padding: 1px 10px 3px; cursor: pointer; margin: 5px 0;\" value=\"Lier à un client\" onclick=\"document.getElementById('suivi_recherche_".$suivi['id']."').style.display = 'block';\">
                            <input type=\"texte\" id=\"suivi_recherche_".$suivi['id']."\" name=\"transfert_suivi_recherche\" style=\"margin: 5px auto; width: 100px; display: none;\" onkeyup=\"checkListClients(this.value, ".$userid.", ".$suivi['id'].",'classique');\">
                            </td>"
                    . "</tr>";*/
        
        $suivi_assoc .= "</table>";
        
        $cdetxt ="";

       	if($suivi['facturid']!=0){
       		$cdetxt.= btn_suivi_cde_a_delier($userid,$suivi);      
       	}
       	
        if($suivi['facturid']==0 && stripos($suivi['suivi'],'[infos_client]') === false){
       	
       		if($nb_cde_client > 0){
       			
       			$suivi_assoc .= btn_suivi_cde_a_lier($DB_site2,$userid,$suivi);
       		}
        }
        $suivi_assoc .=$cdetxt;
        if($suivi['projetid']!=0){

            $liste_projets=$DB_site2->query("   SELECT * FROM (
                (SELECT p.* FROM projet AS p WHERE userid='".$userid."' AND p.projetid != '".$suivi['projetid']."')
                    UNION
                    (SELECT p.* FROM projet AS p INNER JOIN projet_relation AS pr USING(projetid) WHERE pr.userid='".$userid."' AND p.projetid != '".$suivi['projetid']."')
                ) AS T ORDER BY T.libelle ASC ");
                $projet_txt="";
                while($liste_projet=$DB_site2->fetch_array($liste_projets)){
                    $projet_txt.="<option value=\"".$liste_projet['projetid']."\">PJT".$liste_projet['projetid']. " - ".substr($liste_projet['libelle'],0,40)."</option>";
                }

            $projet=$DB_site2->query_first("SELECT * FROM projet WHERE projetid='".$suivi['projetid']."' ");
            $projettxt="<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\" style=\"text-align: center; margin-bottom: 10px;\">"
                        . "<tr>"
                            . "<td style=\"font-weight: bold;\">
                                Projet :
                                </td>
                            </tr>
                            <tr>
                                <td style=\"background-color:#EFEFEF;\">
                                    ".$projet['libelle']."
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <a onclick=\"verif_delier(this)\" class=\"link_btn\">Délier de ce projet</a>
                                    <div class=\"choix_delier\">
                                        <div onclick=\"change_projet(this)\" class=\"link_btn_delier\">Changer de projet</div>
                                        <div class=\"modif_lier\">
                                            <select name=\"projetid\">
                                                ".$projet_txt."
                                            </select>
                                            <input type=\"submit\" onclick=\"change_projet_action(this,".$suivi['id'].",'".$type_suivi."')\" value=\"changer\"/>
                                        </div>
                                        <div onclick=\"delier(".$suivi['id'].",'".$suivi['projetid']."','".$type_suivi."')\" class=\"link_btn_delier\">Délier de ce projet</div>
                                    </div>
                                </td>
                            </tr>
                    </table>";
            $classprojet=" projet_".$suivi['projetid'];
        } else {
            $projettxt="";
            $classprojet=" ssprojet";
        }
        
        if($suivi['projetid']==0 && stripos($suivi['suivi'],'[infos_client]') === false){
            if($nb_projet_client>0){
                $projet_txt="";
                // $liste_projets=$DB_site2->query("SELECT * FROM projet WHERE userid='".$userid."' ORDER BY libelle ASC ");
                $liste_projets=$DB_site2->query(" 	SELECT * FROM (
                                        (SELECT p.* FROM projet AS p WHERE userid='".$userid."' )
                                        UNION
                                        (SELECT p.* FROM projet AS p INNER JOIN projet_relation AS pr USING(projetid) WHERE pr.userid='".$userid."' )
                                    ) AS T ORDER BY T.libelle ASC ");
                while($liste_projet=$DB_site2->fetch_array($liste_projets)){
                    $projet_txt.="<option value=\"".$liste_projet['projetid']."\">".$liste_projet['libelle']."</option>";
                }
                
                $suivi_assoc .= "<a id=\"suivi_projet_".$suivi['id']."\" class=\"link_btn link_btn_suivi\" >Lier à un projet</a>
                    <div id=\"liaison_projet_".$suivi['id']."\">
                        <form id=\"form_".$suivi['id']."\">
                            <select name=\"projetid\" id=\"select_".$suivi['id']."\">
                                ".$projet_txt."
                            </select>
                            <input type=\"button\" value=\"OK\" id=\"btn_associer_".$suivi['id']."\" class=\"link_btn\">
                        </form>
                    </div>
                    <script>
                        \$jq(document).ready(function () {
                            \$jq('#suivi_projet_".$suivi['id']."').click(function(){
                                \$jq('#liaison_projet_".$suivi['id']."').toggle();
                            });
                            \$jq('#liaison_projet_".$suivi['id']."').hide();
                            \$jq('#btn_associer_".$suivi['id']."').click(function(){
                                var projetid=\$jq('#select_".$suivi['id']." :selected').val();
                                \$jq.ajax('ajax/suivi_client_liaison_projet.php?action=assoc&table=suivi_client&suiviid=".$suivi['id']."&projetid='+projetid,   // request url
                                {            
                                    success: function (data, status, xhr) {    // success callback function
                                    
                                        loadsuivi('".$userid."', '".$_GET['request']."','1');
                                       
                                    },
                                    error: function (jqXhr, textStatus, errorMessage) { // error callback 
                                        \$jq('#btn_refresh_suivi_client').text('Erreur !' + errorMessage);
                                    },
                                    beforeSend: function() {
                                        \$jq('#suivi_client table').remove();
                                        \$jq('#suivi_client_append_after').show();
                                        \$jq('#btn_refresh_suivi_client').text('Chargement en cours ...');
                                    }
                                });
                            });
                        });
                    </script>";
            }
        } else {
            $suivi_assoc .= $projettxt
                . "<script>
                      
                </script>";
        }
        /*if ($authDelSuivi==1){
            $suivi_assoc .= "<a href=\"\"><img src=\"images/supprimer.gif\" onclick=\"cleanSuivi(".$userid.",".$suivi['id'].",'')\" width=\"20\"></a>";
        }
        */
        
        
        $formatLigneSuivi = ""
            . "<tr class=\"suivi".$classtype.$classprojet."\" style=\"border-bottom: 1px solid #000;\">"
                . "<td>".$suiviicon[$suivi['moyen']]."<br>".$suivi['commercial']."<br>".date("d/m/Y H:i",$suivi['date'])."</td>"
                . "<td style=\"text-align: center; position: relative;\">".$suivi_assoc.""
                    . "<div style=\"position: relative;\">"
                        . "<div id=\"type_suivi_resultats_".$suivi['id']."\" class=\"type_suivi_resultats\"></div>"
                    . "</div>"
                    . "<div style=\"position: relative;\">"
                        . "<div id=\"type_suivi_clients_".$suivi['id']."\" class=\"type_suivi_resultats\"></div>"
                    . "</div>"
                . "</td>"
                . "<td class=\"suivitxt\">"
                    . $tddest
                    . formatsuivi($suivi['suivi']).$causeperte
                . "</td>"
                . "<td></td>"
                    // "<td>".$etat."</td>"
                . "<td class=\"".rappeltype_class($suivi['rappeltype'])."\">".($suivi['rappel']!=0?rappel_heure($suivi['rappel'],$suivi['jour'],$suivi['rappeltype'],$suivi['projetid'])." par ".$suivi['pour']:"").($suivi['rappeltype']!=""?sprintf(" (%s)",rappeltype_txt($suivi['rappeltype'])):"")."</td>"
            ."</tr>";
        $suivitd[$suivi['date'].uniqid()] = $formatLigneSuivi;
    }
}


$userid=isset($_GET['userid']) ? $_GET['userid'] : false;
$date=isset($_GET['date'])?$_GET['date']:null;
$readonly=false;
$limit=$_GET['all']==0;
$suivitd=array();
$count = 0;
if($limit)
    $nb_count = 5;
else
    $nb_count = INF;
switch ($_GET['request']) {

    case 'int':// Ce qui concerne le compte seulement (modif coordonnée etc...)
        searchSuivi($userid,true,false,$limit,false,false,false,false,true,null,$date);
        break;

    case 'tri':
        searchMail($userid,0,'',0,0,false,$limit,null,$date);
        break;

    case 'pjt':
        if(isset($_GET['pjt']) && $_GET['pjt'] != 'undefined'){
            $pjt = $_GET['pjt'];
        }else{
            $pjt = "*";
        }
        $fourn=isset($_GET['fourn'])?$_GET['fourn']:null;
        $destinataire=isset($_GET['destinataire'])?$_GET['destinataire']:null;
        $interne=isset($_GET['interne'])?$_GET['interne']:true;
        $search=isset($_GET['search'])?$_GET['search']:null;
        searchSuivi($userid,false,$pjt,false,false,false,$fourn,$destinataire,$interne,$search,$date);
        searchMail($userid,null,$fourn,null,$pjt,null,$limit,$destinataire,$date);
        break;
    case 'sav':
        searchMail($userid,null,null,"*",null,null,$limit,null,$date);
        break;
    case 'cde';
    	searchSuivi($userid,false,false,$limit,true,false,false,false,false,null,$date);
        searchMail($userid,"*",null,null,null,null,$limit,null,$date);
        break;		
    case 'appel':
        searchSuivi($userid,false,false,$limit,false,true,false,false,false,null,$date);
        break;
    case 'all':
        searchSuivi($userid,false,null,$limit,false,false,false,false,true,null,$_GET['date']);
        searchMail($userid,null,null,null,null,false,$limit,null,$_GET['date']); 
        break;
        
    default:
        searchSuivi($userid,false,null,$limit,false,false,false,false,true,null,$_GET['date']);
        searchMail($userid,null,null,null,null,false,$limit,null,$_GET['date']);
        break;

}



$return = ""
. "<table>"
	. "<thead style=\"color:black; text-align:center; font-weight:bold;\">"
		. "<tr style=\"border-bottom: 1px solid #000;\">"
			. "<td width=\"10%\">Date</td>"
			. "<td width=\"10%\">Action(s)</td>"
			. "<td width=\"60%\">Suivi</td>"
			. "<td width=\"5%\">PJ</td>"
            //. "<td width=\"5%\">Etat</td>"
			. "<td width=\"5%\">Rappel</td>"
			
		. "</tr>"
	. "</thead>";
if(count($suivitd) == 0){
	$return .= "<tr>
        <td colspan=\"6\" style=\"text-align:center;\">Pas de suivi - <a onclick=\"loadsuivi('".$userid."', '".$_GET['request']."','1',".(isset($_GET['date'])?"'".$_GET['date']."'":"undefined").")\" class=\"link_btn\">Tout afficher</a></td>
    </tr>";
} else {
	// var_dump($suivitd);
    krsort($suivitd);
    foreach($suivitd as $tr){
        if($count<$nb_count){
            $return.=$tr;
            $count++;
        }
    }
    if($limit) $return.="<tr>
        <td colspan=\"6\" style=\"text-align:center;\"><a onclick=\"loadsuivi(".($userid==false?"undefined":$userid).", '".$_GET['request']."','1',".(isset($_GET['pjt'])?$_GET['pjt']:"undefined").",".(isset($_GET['date'])?"'".$_GET['date']."'":"undefined").")\" class=\"link_btn\">Tout afficher</a></td>
    </tr>";
}
$return .= "</table>";

echo $return;

?>
