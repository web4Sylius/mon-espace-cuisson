<?php
require_once($_SERVER['DOCUMENT_ROOT']."/admin/admin_global.php");


$facturid=$_GET['facturid'];
if($facturid==""){
	echo "erreur";
	exit;
}

$usertel=($user_info['tel']!=0);

$suiviicon=array(	"tel_out"=>"<img src=\"images/tel_out.png\"><br>",
					"tel_in"=>"<img src=\"images/tel_in.png\"><br>",
					"mail_in"=>"<img src=\"images/get_mail.png\"><br>");

$linkcolor=$_GET['linkcolor'];

function formatmail_facture($mail,$sens){    
    global $DB_site2;
    global $facturid;
    global $linkcolor;
    global $host;

    $tddest="";
    $suivi_assoc="<td></td>";
    $type="int";
    $contact=array();
    $source=$mail['to'];
    $exp=$mail['from'];
    //if($sens=="in") $source=$mail['from'];
    $mails=preg_split("[,|;]",$source);
    $mailexp=preg_split("[,|;]",$exp);

    if($mail['savid'] != 0){
        $type='sav';
    }elseif ($mail['projetid'] != 0) {
        $type='projet';
    }elseif ($mail['fournisseur'] != '') {
        $type='pro';
    }elseif ($mail['userid'] != 0 ) {
        $type='client';
    }

    foreach($mails as $mailto){

	    $user=$DB_site2->query_first("SELECT userid,mail,telephone,portable,nom,prenom,raisonsociale AS societe FROM utilisateur WHERE mail<>'' AND mail='".addslashes($mailto)."' AND code411=''");
	 
        if($user=="") $user=$DB_site2->query_first("SELECT userid,mail,telephone,'' AS portable,nom,prenom,raisonsociale AS societe FROM facture WHERE mail='".addslashes($mailto)."'");
	  
        $contact=$DB_site2->query_first("SELECT * FROM fournisseur WHERE mail<>'' AND mail='".addslashes($mailto)."'");

	    if($mailto==$contact['mail']){

		  $tddest.="<div class=\"suividest\"><b>Pour :</b><br>".$contact['societe']." ".$contact['prenom']." ".$contact['nom']."
		  <br><span class=\"suivilink\" onclick=\"mail_auto(undefined,'".$contact['mail']."');mail_suivi('suivi_commande','".$mail['fa']."')\">".$contact['mail']."</span>
		  <br><span class=\"suivilink\" onclick=\"suiviauto('tel_out','pro',".$contact['id'].",'Appel téléphone ".$contact['societe']." (".$contact['nom']." ".$contact['prenom'].",".($usertel?"'$contact[telephone]'":"undefined").")');self.location.hash='#suivitop';\">".epigy($contact['telephone'],$contact['langue'])."</span>";
		  if($contact['portable']!="") $tddest.="<br><span class=\"suivilink\" onclick=\"suiviauto('tel_out','pro',".$contact['id'].",'Appel téléphone ".$contact['societe']." (".$contact['nom']." ".$contact['prenom'].")',".($usertel?"'$contact[portable]'":"undefined").");self.location.hash='#suivitop';\">".epigy($contact['portable'],$contact['langue'])."</span>";
		  $tddest.="</div>";

	    }elseif($mailto==$user['mail']){

		  $tddest.="<div class=\"suividest\"><b>Pour :</b><br>".$user['societe']." ".$user['prenom']." ".$user['nom']."
		  <br><span class=\"suivilink\" onclick=\"mail_auto(undefined,'".$user['mail']."');mail_services('$user[langue]','$user[site_code]','adv,commercial,sav');mail_suivi('suivi_commande','".$mail['suiviid']."');\">".$user['mail']."</span>
		  <br><span class=\"suivilink\" onclick=\"suiviauto('tel_out','client',".$user['userid'].",'Appel téléphone ".$user['societe']." (".$user['nom']." ".$user['prenom'].")',".($usertel?"'$user[telephone]'":"undefined").");self.location.hash='#suivitop';\">".epigy($user['telephone'],$contact['langue'])."</span>";

		  if($user['portable']!="") $tddest.="<br><span class=\"suivilink\" onclick=\"suiviauto('tel_out','client',".$user['userid'].",'Appel téléphone ".$user['societe']." (".$user['nom']." ".$user['prenom'].")',".($usertel?"'$user[portable]'":"undefined").");self.location.hash='#suivitop';\">".epigy($user['portable'],$contact['langue'])."</span>";

		  $tddest.="</div>";

	    }else{
		  $tddest.="<div class=\"suividest\">".$mailto."</div>";
		
	    }
    }
    foreach($mailexp as $mailfrom){

        $user=$DB_site2->query_first("SELECT userid,mail,telephone,portable,nom,prenom,raisonsociale AS societe FROM utilisateur WHERE mail<>'' AND mail='".addslashes($mailfrom)."' AND code411=''");
     
        if($user=="") $user=$DB_site2->query_first("SELECT userid,mail,telephone,'' AS portable,nom,prenom,raisonsociale AS societe FROM facture WHERE mail='".addslashes($mailfrom)."'");
      
        $contact=$DB_site2->query_first("SELECT * FROM fournisseur WHERE mail<>'' AND mail='".addslashes($mailfrom)."'");

        if($mailfrom==$contact['mail']){

          $tddest.="<div class=\"suividest\"><b>De :</b><br>".$contact['societe']." ".$contact['prenom']." ".$contact['nom']."
          <br><span class=\"suivilink\" onclick=\"mail_auto(undefined,'".$contact['mail']."');mail_suivi('suivi_commande','".$mail['fa']."')\">".$contact['mail']."</span>
          <br><span class=\"suivilink\" onclick=\"suiviauto('tel_out','pro',".$contact['id'].",'Appel téléphone ".$contact['societe']." (".$contact['nom']." ".$contact['prenom'].",".($usertel?"'$contact[telephone]'":"undefined").")');self.location.hash='#suivitop';\">".epigy($contact['telephone'],$contact['langue'])."</span>";
          if($contact['portable']!="") $tddest.="<br><span class=\"suivilink\" onclick=\"suiviauto('tel_out','pro',".$contact['id'].",'Appel téléphone ".$contact['societe']." (".$contact['nom']." ".$contact['prenom'].")',".($usertel?"'$contact[portable]'":"undefined").");self.location.hash='#suivitop';\">".epigy($contact['portable'],$contact['langue'])."</span>";
          $tddest.="</div>";

        }elseif($mailfrom==$user['mail']){

          $tddest.="<div class=\"suividest\"><b>De :</b><br>".$user['societe']." ".$user['prenom']." ".$user['nom']."
          <br><span class=\"suivilink\" onclick=\"mail_auto(undefined,'".$user['mail']."');mail_services('$user[langue]','$user[site_code]','adv,commercial,sav');mail_suivi('suivi_commande','".$mail['suiviid']."');\">".$user['mail']."</span>
          <br><span class=\"suivilink\" onclick=\"suiviauto('tel_out','client',".$user['userid'].",'Appel téléphone ".$user['societe']." (".$user['nom']." ".$user['prenom'].")',".($usertel?"'$user[telephone]'":"undefined").");self.location.hash='#suivitop';\">".epigy($user['telephone'],$contact['langue'])."</span>";

          if($user['portable']!="") $tddest.="<br><span class=\"suivilink\" onclick=\"suiviauto('tel_out','client',".$user['userid'].",'Appel téléphone ".$user['societe']." (".$user['nom']." ".$user['prenom'].")',".($usertel?"'$user[portable]'":"undefined").");self.location.hash='#suivitop';\">".epigy($user['portable'],$contact['langue'])."</span>";

          $tddest.="</div>";

        }else{
          $tddest.="<div class=\"suividest\">".$mailfrom."</div>";
        
        }
    }
    if($mail['savid'] == 0) {

        $suivi_assoc = "<td style=\"font-weight: bold;\" width=\"10%\">
            <input type=\"button\" name=\"transfert_suivi\" style=\"background-color: #AA1147; border: medium none; border-radius: 10px; color: white; font-size: 10px; font-weight: bold; padding: 1px 10px 3px; cursor: pointer; margin: 5px 1px;\" value=\"Lier à un sav\" onclick=\"CheckListTransferts2('sav', ".$mail['userid'].", ".$mail['id'].",'classique','scom');\">
            <div id=\"lier_sav_scom_".$mail['id']."\" style=\"display:none;\">
                <select id=\"select_lier_sav_scom_".$mail['id']."\" onchange=\"lierMail(".intval($mail['userid']).",this.value, ".$mail['id'].", '".$sens."', 'sav','scom')\"></select>
            </div>  
        </td>";
    }else{
        $suivi_assoc = "<td style=\"font-weight: bold;\"><div style=\"text-align:center;\">SAV : <a href=\"/facture.php?action=editer&facturid=".$mail[savid]."\" target=\"_blank\">".$mail[savid]."</a><br><a id=\"suivi_sav_".$mail['id']."\" class=\"delier_btn\" onclick=\"delierMail(".intval($mail['userid']).",'".$mail['savid']."', ".$mail['id'].", '".$sens."', 'sav');\">Délier de ce sav</a></div></td>";
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

    $sens=="in"?$icon="get_mail.png":$icon="send_mail.png";
    return "<tr id=\"".$type."-".$mail['id']."-".$mail['facturid']."\" class=\"suivi$type\" style=\"border-bottom: 1px solid #000;\">
        <td class=\"suividest\">
            <span style=\"background-color:".$linkcolor[$mail['facturid']].";".($mail['facturid']==$facturid?"color:red;":"")."\">".$mail['facturid']."</span>
            <br><img src=\"images/$icon\"><br>".date("d/m/Y H:i",$mail['date'])."
        </td>
        <td>".$mail['utilisateur']."</td>"
        //.$suivi_assoc
        ."<td>".$tddest."</td>
        <td class=\"suivitxt\" width=\"60%\">".$mail['subject']
            ."<br>".formatsuivi($mail['body'])
            //.($mail['pj']!=""?"<br>PJ : ".$mail['pj']:"")
        ."</td>
        $suivi_assoc
         <td>".$pjlist."
        </td>
    </tr>";
}
function formatsuivi_facture($s){
    global $DB_site2;
    global $facturid;
    global $suiviicon;
    global $linkcolor;

    $contact = array();
    $bgcolor = "#dddddd;";
    $tddest  = "";
    if($s['type'] == "") $s['type'] = "int";
    
    switch($s['type']){
        case "pro":
            $sql = "SELECT * FROM fournisseur WHERE id = ".$s['destinataire'];
            $contact = $DB_site2->query_first($sql);
        break;
        case "client":
            $sql = "SELECT *,userid AS id FROM utilisateur WHERE userid = ".$s['destinataire'];
            $contact = $DB_site2->query_first($sql);
        break;
        case "facture":
            $sql = "SELECT *,facturid AS id FROM facture WHERE facturid = ".$s['facturid'];
            $contact = $DB_site2->query_first($sql);
            
            $s['type'] = "client";
        break;
    }

    $tddest = ""
    . $contact['societe']." ".$contact['prenom']." ".$contact['nom']
    . "<br><span class=\"suivilink\" onclick=\"mail_auto(undefined,'".$contact['mail']."');mail_suivi('suivi_commande',$facturid);\">".$contact['mail']."</span>"
    . "<br><span class=\"suivilink\" onclick=\"suiviauto('tel_out','".$s['type']."',".$contact['id'].",'Appel téléphone ".$contact['societe']." (".$contact['nom']." ".$contact['prenom'].")',".($usertel?"'$contact[telephone]'":"undefined").");\">".epigy($contact['telephone'],$contact['langue'])."</span>";

    if($contact['portable']!="") $tddest .= "<br><span class=\"suivilink\" onclick=\"suiviauto('tel_out','".$s['type']."',".$contact['id'].",'Appel téléphone ".$contact['societe']." (".$contact['nom']." ".$contact['prenom'].",".($usertel?"'$contact[portable]'":"undefined").")');\">".epigy($contact['portable'],$contact['langue'])."</span>";
    if($s['savid'] == 0 && $s['rappeltype'] == 'int' && is_sav($s['facturid'],$DB_site2)){
        $liersuivi = "<br><input type=\"button\" style=\"background-color: #AA1147; border: medium none; border-radius: 10px; color: white; font-size: 10px; font-weight: bold; padding: 1px 10px 3px; cursor: pointer; margin: 5px 1px;\" value=\"lier à un sav\" onclick=\"CheckListTransferts2('sav', ".$s['facturid'].", ".$s['id'].",'suiviint','suiviint');\">";
        $liersuivi .="<div id=\"lier_sav_suiviint_".$s['id']."\" style=\"display:none;\"> <select id=\"select_lier_sav_suiviint_".$s['id']."\" onchange=\"lierSuivi('sav',this.value,".intval($s['id']).",'suiviint')\"></select></div>";
    }elseif($s['savid'] != 0){
        $liersuivi ="<br><div style=\"text-align:center;\">SAV : <a href=\"/facture.php?action=editer&facturid=".$s[savid]."\" target=\"_blank\">".$s[savid]."</a><br><a id=\"suivi_sav_".$s['id']."\" class=\"delier_btn\" onclick=\"delierSuivi('sav',".intval($s['savid']).",'".$s['id']."','suiviint');\">Délier de ce sav</a></div>";
    }else{
        $liersuivi="";
    }

    return "<tr id=\"".$s['type']."-".$s['id']."-".$s['facturid']."\" class=\"suivi$s[type]\" style=\"border-bottom: 1px solid #000;\">"
        . "<td>
            <span style=\"background-color:".$linkcolor[$s['facturid']].";".($s['facturid']==$facturid?"color:red;":"")."\">".$s['facturid']."</span>
            <br>".$suiviicon[$s['moyen']].date("d/m/Y H:i",$s['date'])
        ."</td>"
        . "<td>".$s['commercial']."</td>"
        . "<td>".$tddest."</td>"
        . "<td class=\"suivitxt\" width=\"60%\">".formatsuivi($s['suivi'])."</td>"
        . "<td".($s['rappel']>$today && $s['rappel']<$tomorrow?" style=\"color:red;\"":"")." class=\"".rappeltype_class($s['rappeltype'])."\">".($s['rappel']!=0?"A rappeler le ".rappel_heure($s['rappel'],$s['jour'])." par ".$s['pour']:"").($s['rappeltype']!=""?sprintf(" (%s)",rappeltype_txt($s['rappeltype'])):"").$liersuivi."</td>"
    . "</tr>";

}


function suivi_commande($facturid,$savlinks, $request, $nomfourn = null){
    global $DB_site2;
    global $suiviicon; // a déclarer
    global $usertel; // a déclarer
    global $linkcolor; // a déclarer
    $suivitd=array();


    $userid = $DB_site2->query_first("SELECT userid FROM facture WHERE facturid='".$facturid."'");

    switch ($request) {

        case 'all': /* ALL ******************************/
            $sql = "SELECT *";
            $sql .= " FROM suivi_commande";
            $sql .= " WHERE facturid = '".$facturid."'";
            $sql .= " OR facturid IN (".$savlinks.") ORDER BY date DESC";
            $rq_suivi = $DB_site2->query($sql);

            while($s = $DB_site2->fetch_array($rq_suivi)){

                $suivitd[$s['date'].uniqid()] = formatsuivi_facture($s);
            }

            $requete=$DB_site2->query("SELECT * FROM mail_outbox WHERE (facturid=$facturid OR facturid IN (".$savlinks."))");
            while($mail=$DB_site2->fetch_array($requete)){
                if(isset($suivitd[$mail['date']])) $mail['date']++;
                $suivitd[$mail['date'].uniqid()]=formatmail_facture($mail,"out");
            }

            
            $requete=$DB_site2->query("SELECT * FROM mail_inbox WHERE (facturid=$facturid OR facturid IN (".$savlinks."))");
            while($mail=$DB_site2->fetch_array($requete)){
                $suivitd[$mail['date'].uniqid()]=formatmail_facture($mail,"in");
            }
            
            $requete=$DB_site2->query("SELECT * FROM envoi_devis WHERE facturid=$facturid OR facturid IN (".$savlinks.")");
            while($envoi=$DB_site2->fetch_array($requete)){
                if($envoi['utilisateur']=="") $envoi['utilisateur']="Le client";
                $suivitd[$envoi['date'].uniqid()]="<tr id=\"int-".$envoi['id']."-".$envoi['facturid']."\" class=\"suiviint\" style=\"border-bottom: 1px solid #000;\">
                    <td><span style=\"background-color:".$linkcolor[$facturid].";".($envoi['facturid']==$facturid?"color:red;":"")."\">".$envoi['facturid']."</span><br>".$envoi['utilisateur']."<br>".date("d/m/Y H:i",$envoi['date'])."</td>
                    <td class=\"suivitxt\" width=\"60%\">Envoi de devis</td>
                    <td></td>
                </tr>";
            }
            break;


        
        case 'cde':
            $where = " AND savid=0";
            $requete_out=$DB_site2->query("SELECT * FROM mail_outbox WHERE (facturid=$facturid OR facturid IN (".$savlinks."))".$where);
            $requete_in=$DB_site2->query("SELECT * FROM mail_inbox WHERE (facturid=$facturid OR facturid IN (".$savlinks."))".$where);

            $rows = $DB_site2->num_rows($requete_out) + $DB_site2->num_rows($requete_in) ;

            if($rows == 0){
                $suivitd[$mail['date'].uniqid()]="<tr><td colspan=\"5\"><p style=\"text-align:center;\">Aucun resultat</p></td></tr>";
            }else{

                while($mail=$DB_site2->fetch_array($requete_out)){
                    if(isset($suivitd[$mail['date']])) $mail['date']++;
                        $suivitd[$mail['date'].uniqid()]=formatmail_facture($mail,"out");
                }

                while($mail=$DB_site2->fetch_array($requete_in)){
                    $suivitd[$mail['date'].uniqid()]=formatmail_facture($mail,"in");
                }  
            }
            break;



        case 'sav': // SAV ********************

            $savid = $DB_site2->query_first("SELECT id FROM sav WHERE facturid='".$facturid."'");
            if($savid[0] == ''){
                $suivitd[$mail['date'].uniqid()]="<tr><td colspan=\"5\"><p style=\"text-align:center;\">Aucun resultat</p></td></tr>";
            }else{
                $savid = $savid[0];
                $where = "AND savid='".$savid."' ";
                $requete=$DB_site2->query("SELECT * FROM mail_outbox WHERE (facturid=$facturid OR facturid IN (".$savlinks."))".$where);
                while($mail=$DB_site2->fetch_array($requete)){
                    if(isset($suivitd[$mail['date']])) $mail['date']++;
                    $suivitd[$mail['date'].uniqid()]=formatmail_facture($mail,"out");
                }

                
                $requete=$DB_site2->query("SELECT * FROM mail_inbox WHERE (facturid=$facturid OR facturid IN (".$savlinks."))".$where);
                while($mail=$DB_site2->fetch_array($requete)){
                    $suivitd[$mail['date'].uniqid()]=formatmail_facture($mail,"in");
                }

                $requete=$DB_site2->query("SELECT id,date,suivi,savid,type,destinataire,commercial,facturid FROM suivi_commande WHERE facturid='$facturid' AND savid != 0");
                while($suivi=$DB_site2->fetch_array($requete)){
                    $suivitd[$suivi['date'].uniqid()]=formatsuivi_facture($suivi);
                }
                if(count($suivitd) == 0 ) $suivitd[$mail['date'].uniqid()]="<tr><td colspan=\"5\"><p style=\"text-align:center;\">Aucun resultat</p></td></tr>";
            }

            break;


        case 'fourn':
            $where = " AND facturid ='".$facturid."' AND fournisseur ='$nomfourn' ";
            
            $requete_out=$DB_site2->query("SELECT * FROM mail_outbox WHERE (facturid=$facturid OR facturid IN (".$savlinks."))".$where);
            $requete_in=$DB_site2->query("SELECT * FROM mail_inbox WHERE (facturid=$facturid OR facturid IN (".$savlinks."))".$where);

            $rows = $DB_site2->num_rows($requete_out) + $DB_site2->num_rows($requete_in) ;

            if($rows == 0){
                $suivitd[$mail['date'].uniqid()]="<tr><td colspan=\"5\"><p style=\"text-align:center;\">Aucun resultat</p></td></tr>";
            }else{

                while($mail=$DB_site2->fetch_array($requete_out)){
                    if(isset($suivitd[$mail['date']])) $mail['date']++;
                    $suivitd[$mail['date'].uniqid()]=formatmail_facture($mail,"out");
                }

                while($mail=$DB_site2->fetch_array($requete_in)){
                    $suivitd[$mail['date'].uniqid()]=formatmail_facture($mail,"in");
                }  
            }
            
            break;
        
         case 'compta':
            $where = " AND facturid='".$facturid."' AND compta = 'oui' ";
            $requete_out=$DB_site2->query("SELECT * FROM mail_outbox WHERE (facturid=$facturid OR facturid IN (".$savlinks."))".$where);
            $requete_in=$DB_site2->query("SELECT * FROM mail_inbox WHERE (facturid=$facturid OR facturid IN (".$savlinks."))".$where);

            $rows = $DB_site2->num_rows($requete_out) + $DB_site2->num_rows($requete_in) ;

            if($rows == 0){
                $suivitd[$mail['date'].uniqid()]="<tr><td colspan=\"5\"><p style=\"text-align:center;\">Aucun resultat</p></td></tr>";
            }else{

                while($mail=$DB_site2->fetch_array($requete_out)){
                    if(isset($suivitd[$mail['date']])) $mail['date']++;
                        $suivitd[$mail['date'].uniqid()]=formatmail_facture($mail,"out");
                }

                while($mail=$DB_site2->fetch_array($requete_in)){
                    $suivitd[$mail['date'].uniqid()]=formatmail_facture($mail,"in");
                }  
            }
            break;

        case 'int' :

            $sql = "SELECT * FROM suivi_commande WHERE facturid = '".$facturid."' OR facturid IN (".$savlinks.") ORDER BY date DESC";
            $rq_suivi = $DB_site2->query($sql);
            if($DB_site2->num_rows($rq_suivi) == 0){
                $suivitd[$mail['date'].uniqid()]="<tr><td colspan=\"5\"><p style=\"text-align:center;\">Aucun resultat</p></td></tr>";
            }else{
                while($s = $DB_site2->fetch_array($rq_suivi)){
                    $suivitd[$s['date'].uniqid()] = formatsuivi_facture($s);
                }
            }
            break;    
    }

	krsort($suivitd);

    return $suivitd;
}
function is_sav($facturid,$DB){
    $facturid = intval($facturid);
    $issav = $DB->query("SELECT id FROM sav WHERE facturid ='$facturid'");
    if($DB->num_rows($issav) > 0)
        return true;
    else
        return false;
}


/*

function compte_suivi_commande($facturid,$savlinks){
    global $DB_site2;
	$nb_interne=0;
	$nb_pro=0;
	$nb_client=0;
	$sql = "SELECT *";
    $sql .= " FROM suivi_commande";
    $sql .= " WHERE facturid = '".$facturid."'";
    $sql .= " OR facturid IN (".implode(",",$savlinks).") ORDER BY date DESC";
    $rq_suivi = $DB_site2->query($sql);
    while($s = $DB_site2->fetch_array($rq_suivi)){
        if($s['type'] == "") $s['type'] = "int";
        
        switch($s['type']){
            case "pro":
                $nb_pro++;
            break;
            case "client":
                $nb_client++;
            break;
			case "int":
                $nb_interne++;
            break;
            case "facture":
                $nb_client++;
            break;
        }
	}
    $nb_mail_out=$DB_site2->query_first("SELECT COUNT(*) FROM mail_outbox WHERE suivitype='suivi_commande' AND (suiviid=".$facturid." OR suiviid IN (".implode(',',$savlinks)."))");
    $nb_mail_in=$DB_site2->query_first("SELECT COUNT(*) FROM mail_inbox WHERE suivitype='suivi_commande' AND (suiviid=".$facturid." OR suiviid IN (".implode(',',$savlinks)."))");
    $nb_devis=$DB_site2->query_first("SELECT COUNT(*) FROM envoi_devis WHERE facturid=".$facturid." OR facturid IN (".implode(',',$savlinks).")");
	$nb_interne = $nb_interne + $nb_mail_out[0] + $nb_mail_in[0] + $nb_devis[0];
	$retour = array("nb_interne" => $nb_interne, "nb_pro" => $nb_pro, "nb_client" => $nb_client);
    return $retour;
}
*/


$_GET['namefourn'] = str_replace("-", "'", $_GET['namefourn']);

$echo = suivi_commande($facturid,$_GET['$savlinks'],$_GET['request'],addslashes($_GET['namefourn']));

if($echo!=""){
	
	foreach ($echo as $key => $value) {
		echo $value;
	}

} else {
	echo "<tr><td colspan=\"5\">aucun suivi commercial sur cette commande.</td></tr>";
}
?>