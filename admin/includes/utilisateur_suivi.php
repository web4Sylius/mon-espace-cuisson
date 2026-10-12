<?php

/*

ce fichier ne sert plus !!!! => aller voir /admin/ajax/suivi_client.php





<script type="text/javascript">
function mouseOver(id) {
    document.getElementById(id).style.display = "block";
}

function mouseOut(id) {
    document.getElementById(id).style.display = "none";
}
</script>
<?php
$suiviicon = array(
	"tel_out" => "<img src=\"images/tel_out.png\"><br>",
	"tel_in"  => "<img src=\"images/tel_in.png\"><br>",
	"mail_in" => "<img src=\"images/get_mail.png\"><br>"
);

function suivi_client($userid,$readonly=false){
    global $DB_site2;
    global $authDelSuivi;
    global $suiviicon;

    $sql = "SELECT *";
    $sql .= " FROM suivi_client";
    $sql .= " WHERE userid = ".$userid;
    $sql .= " ORDER BY date DESC";
    $requete = $DB_site2->query($sql);

    while($suivi = $DB_site2->fetch_array($requete)){

		$causeperte = "";

		switch($suivi['etat']){
		    
		    case 0:

				$etat  = "<span style=\"color:red;\">Client perdu</span>";

				//Il suffit que le délai d'enregistrement dans "causeperte" ait pris 1 seconde de plus que dans suivi_client, la liaison ne se fait plus. Il faut donc rajouter une fourchette de 10 secondes pour tomber plus juste... (la liaison aurait du se faire sur l'id de causeperte...)
				$sql = "SELECT *";
				$sql .= " FROM causeperte";
				$sql .= " WHERE date >= " . ($suivi['date'] - 5); 
				$sql .= " AND date <= " . ($suivi['date'] + 5);
				$sql .= " AND userid = ".$userid;
				$cause = $DB_site2->query_first($sql);
			    
			    if($cause['article'] != "" || $cause['marque'] != ""){
					$causeperte .= "<br>Achat ".$cause['article']." ".$cause['marque'];
					if($cause['prix'] != "") $causeperte .= " prix : ".$cause['prix'];
			    }
			    if($cause['lieu'] != ""){
					if($causeperte == "") $causeperte = "<br>Achat";
					$causeperte .= " chez ".$cause['lieu'];
			    }

			    switch($cause['cause']){
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
			    $etat = "<span style=\"color:#00FF00;\">En commande</span>";
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

		//Récupération des suivis dupliqués
		$sql = "SELECT *";
		$sql .= " FROM suivi_duplication";
		$sql .= " WHERE suivi_origine_id = ".$suivi['id'];
		$sql .= " AND suivi_origine_type = 'client'";
		$sql .= " ORDER BY suivi_copie_type, copie_id";
		$req = $DB_site2->query($sql);
		$cpt = 1;
		$suivi_assoc = '';
		$remType = '';
		while($row = $DB_site2->fetch_array($req)){
			
			if($cpt == 1) $suivi_assoc = ""
				. "<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\" style=\"text-align: center; margin-bottom: 10px;\">"
					. "<tr>"
						. "<td style=\"background-color: #363E42; color: white; font-weight: bold;\">Associé à</td>"
					. "</tr>";
			
			$initType = false;
			if($remType == '' || $row['suivi_copie_type'] != $remType){
				$initType = true;
				$remType = $row['suivi_copie_type'];
			} 

			switch($row['suivi_copie_type']){
				
				case 'cmd':

					if($initType){
						$suivi_assoc .= ""
						. "<tr>"
							. "<td style=\"font-weight: bold;\">Commande(s)</td>"
						. "</tr>";
					}
					$lien_page = BACK_URL.'/facture.php?action=editer&facturid=';
					
					break;

				case 'cli':
					
					if($initType){
						$suivi_assoc .= ""
						. "<tr>"
							. "<td style=\"font-weight: bold;\">Client(s)</td>"
						. "</tr>";
					}
					$lien_page = BACK_URL.'/utilisateur.php?action=editer&userid=';

					break;
			}

			$suivi_assoc .= ""
			. "<tr>"
				. "<td style=\"background-color: ".($cpt%2 == 0 ? '#E0E0E0' : '#EFEFEF').";\"><a href=\"".$lien_page.$row['copie_id']."\" target=\"_blank\">".$row['copie_id']."</a></td>"
			. "</tr>";

			if($cpt == $DB_site2->num_rows($req)) $suivi_assoc .= "</table>";

			$cpt++;
		}

    $formatLigneSuivi = "";
		$formatLigneSuivi = ""
		. "<tr class=\"suivi".$classtype."\">"
			. "<td>".$suiviicon[$suivi['moyen']]."<br>".$suivi['commercial']."<br>".date("d/m/Y H:i",$suivi['date'])."</td>"
			. "<td class=\"suivitxt\">"
			. $tddest
			.formatsuivi($suivi['suivi']).$causeperte
			."</td>"
			. "<td></td>"
			. "<td>".$etat."</td>"
			. "<td class=\"".rappeltype_class($suivi['rappeltype'])."\">".($suivi['rappel']!=0?rappel_heure($suivi['rappel'],$suivi['jour'])." par ".$suivi['pour']:"").($suivi['rappeltype']!=""?sprintf(" (%s)",rappeltype_txt($suivi['rappeltype'])):"")."</td>";
    $formatLigneSuivi .= ""  
			. "<td style=\"text-align: center; position: relative;\">"
				. $suivi_assoc
				. "<input type=\"button\" name=\"transfert_suivi\" style=\"background-color: #AA1147; border: medium none; border-radius: 10px; color: white; font-size: 10px; font-weight: bold; padding: 1px 10px 3px; cursor: pointer; margin: 5px 0;\" value=\"Lier à une commande\" onclick=\"CheckListTransferts('cmd', ".$userid.", ".$suivi['id'].",'classique');\">"
				. "<div style=\"position: relative;\">"
					. "<div id=\"type_suivi_resultats_".$suivi['id']."\" class=\"type_suivi_resultats\"></div>"
				. "</div>"
				. "<input type=\"button\" name=\"transfert_suivi\" style=\"background-color: #AA1147; border: medium none; border-radius: 10px; color: white; font-size: 10px; font-weight: bold; padding: 1px 10px 3px; cursor: pointer; margin: 5px 0;\" value=\"Lier à un client\" onclick=\"document.getElementById('suivi_recherche_".$suivi['id']."').style.display = 'block';\">"
				. "<input type=\"texte\" id=\"suivi_recherche_".$suivi['id']."\" name=\"transfert_suivi_recherche\" style=\"margin: 5px auto; width: 100px; display: none;\" onkeyup=\"checkListClients(this.value, ".$userid.", ".$suivi['id'].",'classique');\">"
				. "<div style=\"position: relative;\">"
					. "<div id=\"type_suivi_clients_".$suivi['id']."\" class=\"type_suivi_resultats\"></div>"
				. "</div>";
        
    // TSI gestion de l'affichage suppression d'un suivi / mail pour SU et utilisateur_admin       
    if ($authDelSuivi==1)  
        //$formatLigneSuivi .= "<a href=\"delSuivi.php?userid=".$userid."&suividi=".$suivi['id']."&mailid=\"><img src=\"images/supprimer.gif\" onclick=\"delSuivi.php?userid=".$userid."&mailid=".$mail['id']."\" width=\"20\"></a>";
        $formatLigneSuivi .= "<a href=\"\"><img src=\"images/supprimer.gif\" onclick=\"cleanSuivi(".$userid.",".$suivi['id'].",'')\" width=\"20\"></a>";
        
		$formatLigneSuivi .= "</td></tr>";
    
    $suivitd[$suivi['date'].uniqid()] = $formatLigneSuivi;
    }

    $sql = "SELECT *";
    $sql .= " FROM mail_outbox";
    $sql .= " WHERE suivitype = 'suivi_client'";
    $sql .= " AND suiviid = ".$userid;
    $requete = $DB_site2->query($sql);
    while($mail = $DB_site2->fetch_array($requete)){
		$suivitd[$mail['date'].uniqid()] = formatmail($mail,"out",$readonly,$userid,'mail_outbox');
    }

    $sql = "SELECT *";
    $sql .= " FROM mail_inbox";
    $sql .= " WHERE suivitype = 'suivi_client'";
    $sql .= " AND suiviid = ".$userid;
    $requete = $DB_site2->query($sql);
    while($mail = $DB_site2->fetch_array($requete)){
		$suivitd[$mail['date'].uniqid()] = formatmail($mail,"in",$readonly,$userid,'mail_inbox');
    }

    if(count($suivitd) == 0){
		
		$return = "<p align=\"center\">Pas de suivi</p>";

    } else {

		$return = ""
	. "<table>"
		. "<thead style=\"color:black; text-align:center; font-weight:bold;\">"
			. "<tr>"
				. "<td width=\"10%\">Date</td>"
				. "<td width=\"60%\">Suivi</td>"
				. "<td width=\"5%\">PJ</td>"
				. "<td width=\"5%\">Etat</td>"
				. "<td width=\"5%\">Rappel</td>"
				. "<td width=\"10%\">Action(s)</td>"
			. "</tr>"
		. "</thead>";

	krsort($suivitd);
	foreach($suivitd as $tr) $return.=$tr;
	
	$return .= "</table>";

    }
    
    return $return;
}

function formatmail($mail,$sens,$readonly=false,$userid,$type_suivi){    
    
    global $DB_site2;
    global $linkcolor;
    global $authDelSuivi;

	$type    = "int";
	$contact = array();
	$td=array();
	
    foreach(array("from","to") as $mailtype){
        if(!isset($mail[$mailtype])) continue;
        $mails = preg_split("[,|;]",$mail[$mailtype]);
        $td[$mailtype]="";
        $virg="";
        $type="";
        foreach($mails as $mailaddr){
            
            $sql = "SELECT userid,mail,telephone,portable,nom,prenom,raisonsociale AS societe";
            $sql .= " FROM utilisateur";
            $sql .= " WHERE mail <> ''";
            $sql .= " AND mail = '".addslashes($mailaddr)."'";
            $user = $DB_site2->query_first($sql);

            $usercontact=$DB_site2->query_first("SELECT * FROM utilisateur_assoc WHERE utilisateur_assoc_email='".mysql_real_escape_string($mailaddr)."'");
            
            $sql = "SELECT *";
            $sql .= " FROM fournisseur";
            $sql .= " WHERE mail <> ''";
            $sql .= " AND mail = '".addslashes($mailaddr)."'";
            $contact = $DB_site2->query_first($sql);

            if($mailaddr == $contact['mail']){

                $td[$mailtype] .= $virg
                . "<div>".$contact['societe']." ".$contact['prenom']." ".$contact['nom']
                . "<span".($readonly?"":" class=\"suivilink\" onclick=\"mail_auto(undefined,'".$contact['mail']."');mail_suivi('suivi_commande','".$mail['suiviid']."')\"").">".$contact['mail']."</span>"
                . "<span".($readonly?"":" class=\"suivilink\" onclick=\"suiviauto('tel_out','pro',".$contact['id'].",'Appel téléphone ".$contact['societe']." (".$contact['nom']." ".$contact['prenom'].",".($usertel?"'$contact[telephone]'":"undefined").")');self.location.hash='#suivitop';\"").">".$contact['telephone']."</span>";

                if($contact['portable'] != "") $td[$mailtype].="<span".($readonly?"":" class=\"suivilink\" onclick=\"suiviauto('tel_out','pro',".$contact['id'].",'Appel téléphone ".$contact['societe']." (".$contact['nom']." ".$contact['prenom'].")',".($usertel?"'$contact[portable]'":"undefined").");self.location.hash='#suivitop';\"").">".$contact['portable']."</span>";

                $td[$mailtype] .= "</div>";
                $type    = "pro";

            } elseif($mailaddr == $user['mail']){

                $td[$mailtype] .= $virg
                . "<div>".$user['societe']." ".$user['prenom']." ".$user['nom']
                . "<span".($readonly?"":" class=\"suivilink\" onclick=\"mail_auto(undefined,'".$user['mail']."');mail_services('$user[langue]','$user[site_code]','adv,commercial');\"").">".$user['mail']."</span>"
                . "<span".($readonly?"":" class=\"suivilink\" onclick=\"suiviauto('tel_out','client',".$user['userid'].",'Appel téléphone ".$user['societe']." (".$user['nom']." ".$user['prenom'].")',".($usertel?"'$user[telephone]'":"undefined").");self.location.hash='#suivitop';\"").">".$user['telephone']."</span>";

                if($user['portable'] != "") $td[$mailtype] .= "<span".($readonly?"":" class=\"suivilink\" onclick=\"suiviauto('tel_out','client',".$user['userid'].",'Appel téléphone ".$user['societe']." (".$user['nom']." ".$user['prenom'].")',".($usertel?"'$user[portable]'":"undefined").");self.location.hash='#suivitop';\"").">".$user['portable']."</span>";

                $td[$mailtype] .= "</div>";
                $type    = "client";

            } elseif($mailaddr==$usercontact['utilisateur_assoc_email']) {
                $user=$DB_site2->query_first("SELECT * FROM utilisateur WHERE userid=".$usercontact['userid']);
                $td[$mailtype] .= $virg
                . "<div>".$usercontact['utilisateur_assoc_prenom']." ".$usercontact['utilisateur_assoc_nom']
                . "<span".($readonly?"":" class=\"suivilink\" onclick=\"mail_auto(undefined,'".$usercontact['utilisateur_assoc_email']."');mail_services('$user[langue]','$user[site_code]','adv,commercial');\"").">".$usercontact['utilisateur_assoc_email']."</span>"
                . "<span".($readonly?"":" class=\"suivilink\" onclick=\"suiviauto('tel_out','client',".$user['userid'].",'Appel téléphone ".$user['societe']." (".$usercontact['utilisateur_assoc_nom']." ".$usercontact['utilisateur_assoc_prenom'].")',".($usertel?"'$usercontact[utilisateur_assoc_tel]'":"undefined").");self.location.hash='#suivitop';\"").">".$usercontact['utilisateur_assoc_tel']."</span>
                </div>";
                
                $type    = "client";
                
            } else {

                $td[$mailtype] .= $virg."<div>".$mailaddr."</div>";
                $type    = "int";
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

            $valeur = ROOT_PATH."admin/mail/attachments/".$pj['id'].".".$pj['ext'];
            if (file_exists($valeur)){
                $pjlist .= "<form method=\"post\" style=\"margin: 0px;\" align=\"center\" type=\"button\" action=\"/admin/includes/download.php\">
            <input type=\"submit\" value=\" \" onmouseover=\"mouseOver('special".$i."')\" onmouseout=\"mouseOut('special".$i."')\" class=\"dlbutton\"/>
            <input type=\"hidden\" name=\"page\" value=\"".$valeur."\"/>
            </form> <div id=\"special".$i."\" class=\"spehover\">".$pj['name']."</div>";
            }
            $i++;
        }
    }

    $sens == "in" ? $icon = "get_mail.png" : $icon = "send_mail.png";

    $content_div = ""
		. "<div class=\"transfer\" onclick=\"reply(".$mail['id'].",'".$sens."box');\" title=\"Transférer\"></div>"
		. "<div class=\"reply\" onclick=\"reply(".$mail['id'].",'".$sens."box',1);\" title=\"Répondre\"></div>"
		. "<div class=\"clear\"></div>";

	//Récupération des suivis dupliqués
	$sql = "SELECT *";
	$sql .= " FROM suivi_duplication";
	$sql .= " WHERE suivi_origine_id = ".$mail['id'];
	$sql .= " AND suivi_origine_type = 'client'";
	$sql .= " ORDER BY suivi_copie_type, copie_id";
	$req = $DB_site2->query($sql);
	$cpt = 1;
	$suivi_assoc = '';
	$remType = '';
	while($row = $DB_site2->fetch_array($req)){

		if($cpt == 1) $suivi_assoc = ""
			. "<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\" style=\"text-align: center; margin-bottom: 10px;\">"
				. "<tr>"
					. "<td style=\"background-color: #363E42; color: white; font-weight: bold;\">Associé à</td>"
				. "</tr>";
		
		$initType = false;
		if($remType == '' || $row['suivi_copie_type'] != $remType){
			$initType = true;
			$remType = $row['suivi_copie_type'];
		} 

		switch($row['suivi_copie_type']){
			
			case 'cmd':

				if($initType){
					$suivi_assoc .= ""
					. "<tr>"
						. "<td style=\"font-weight: bold;\">Commande(s)</td>"
					. "</tr>";
				}
				$lien_page = BACK_URL.'/facture.php?action=editer&facturid=';
				
				break;

			case 'cli':
				
				if($initType){
					$suivi_assoc .= ""
					. "<tr>"
						. "<td style=\"font-weight: bold;\">Client(s)</td>"
					. "</tr>";
				}
				$lien_page = BACK_URL.'/utilisateur.phpaction=editer&userid=';

				break;
		}

		$suivi_assoc .= ""
		. "<tr>"
			. "<td style=\"background-color: ".($cpt%2 == 0 ? '#E0E0E0' : '#EFEFEF').";\"><a href=\"".$lien_page.$row['copie_id']."\" target=\"_blank\">".$row['copie_id']."</a></td>"
		. "</tr>";

		if($cpt == $DB_site2->num_rows($req)) $suivi_assoc .= "</table>";

		$cpt++;
	}
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
	
    $affMail = ""
      . "<tr id=\"".$type."-".$mail['id']."-".$mail['suiviid']."\" class=\"suivi$type\"><script type=\"text/javascript\">resizeImgSuivi();</script>"
	    . "<td><img src=\"images/$icon\"><br>".$mail['utilisateur']."<br>".date("d/m/Y H:i",$mail['date'])."</td>"
	    . "<td class=\"suivitxt\">";
	    if(isset($td['from']) && !empty($td['from']))
            $affMail .="<div class=\"addr\">De : ".$td['from']."</div>";
	    
	    $affMail .="<div class=\"addr\">Pour : ".$td['to']."</div>"
	    . "<div class=\"subject\">Sujet : <b>".$mail['subject'].($readonly ? "" : $content_div)."</b></div>"
	    . formatsuivi($mail['body'])
	    . ($mail['pj'] != "" ? "<br>PJ : ".str_replace(","," | ",$mail['pj']):"")
        . "</td>"
	    . "<td>".$aff.$pjlist."</td>"
	    ."<td></td>"
	    . "<td></td>"
	    . "<td style=\"text-align: center;\">"
			. $suivi_assoc
			. "<input type=\"button\" name=\"transfert_suivi\" style=\"background-color: #AA1147; border: medium none; border-radius: 10px; color: white; font-size: 10px; font-weight: bold; padding: 1px 10px 3px; cursor: pointer; margin: 5px 0;\" value=\"Lier à une commande\" onclick=\"CheckListTransferts('cmd', ".$userid.", ".$mail['id'].", '".$type_suivi."');\">"
			. "<div style=\"position: relative;\">"
			. "<div id=\"type_suivi_resultats_".$mail['id']."\" class=\"type_suivi_resultats\"></div>"
			. "</div>"
			. "<input type=\"button\" name=\"transfert_suivi\" style=\"background-color: #AA1147; border: medium none; border-radius: 10px; color: white; font-size: 10px; font-weight: bold; padding: 1px 5px 3px; cursor: pointer; margin: 5px 0;\" value=\"Lier à un client\" onclick=\"document.getElementById('suivi_recherche_".$mail['id']."').style.display = 'block';\">"
			. "<input type=\"texte\" id=\"suivi_recherche_".$mail['id']."\" name=\"transfert_suivi_recherche\" style=\"margin: 5px auto; width: 100px; display: none;\" onkeyup=\"checkListClients(this.value, ".$userid.", ".$mail['id'].", '".$type_suivi."');\">"
			. "<div style=\"position: relative;\">"
			. "<div id=\"type_suivi_clients_".$mail['id']."\" class=\"type_suivi_resultats\"></div>"
			. "</div>";
    
    // TSI gestion de l'affichage suppression d'un suivi / mail pour SU et utilisateur_admin
    if ($authDelSuivi==1)  
        //$affMail .= "<a href=\"delSuivi.php?userid=".$userid."&suividi=&mailid=".$mail['id']."\"><img src=\"images/supprimer.gif\" onclick=\"delSuivi.php?userid=".$userid."&mailid=".$mail['id']."\" width=\"20\"></a>";
        $affMail .= "<a href=\"\"><img src=\"images/supprimer.gif\" onclick=\"cleanSuivi(".$userid.",'',".$mail['id'].")\" width=\"20\"></a>";
    $affMail .= "</td></tr>";
    
    return $affMail;
}

?>
<script type="text/javascript">
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
</script>


*/

?>