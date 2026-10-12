<?php

require_once "admin_global.php";
require_once "taskmanager/taskReminder.class.php";
include_once("../class/projet.class.php");


$user_rappel=$user_info['username'];
if(isset($_GET['user_rappel'])) $user_rappel=$_GET['user_rappel'];

$ts=mktime(0, 0, 0, 7, 1, 2025)+60;
$from=mktime(0,0,0);
$fromdisplay=$from-86400*100;
$to=mktime(23,59,59);
$alertts=mktime(date("H"),date("i"),0);

if(isset($_GET['hide2'])) $DB_site2->query("UPDATE suivi_client SET hide=1 WHERE id=".$_GET['hide2']);

if(isset($_GET['hide3'])) $DB_site2->query("UPDATE crm_calls SET hide=1 WHERE id='".$_GET['hide3']."'");

if(isset($_GET['hide6'])) $DB_site2->query("UPDATE suivi_interne_log SET hide=1 WHERE id='".$_GET['hide6']."'");

$rappels = $rappels3 = array();
$alert   = "";
$alertid = 0;

//Tableau qui va servir à trier les suivis par date ET par demi-journée (AM/PM)
$joursh = array(
    1 => 9,
    2 => 14
);


$rappels2 = array();

if($user_rappel == null) $user_rappel = $user_info['username'];

$ts   = mktime(0, 0, 0, 7, 1, 2025) + 60;
$from = mktime(0,0,0);
$to   = mktime(23,59,59);

//Tableau qui va servir à trier les suivis par date ET par demi-journée (AM/PM)
$joursh = array(
    1 => 9,
    2 => 14
);

if(isset($_GET['hide'])) $DB_site2->query("UPDATE suivi_commande SET hide=1 WHERE id=".$_GET['hide']);
if(isset($_GET['hide4'])) $DB_site2->query("UPDATE sav_steps SET hide=1 WHERE id=".$_GET['hide4']);
if(isset($_GET['hide5'])) $DB_site2->query("UPDATE suivi_stock SET hide=1 WHERE id='".$_GET['hide5']."'");



//**********************//
// RDV DU JOUR - PROJET //
//********************//

if($user_info['service'] == '1' || $user_info['username']=='philippe' || $user_info['username']=='jessica'){
    $u=array();
    $today=time();
    $requete=$DB_site2->query("SELECT p.*, u.prenom, u.nom, u.raisonsociale FROM projet p INNER JOIN utilisateur u ON u.userid = p.userid WHERE p.rappel >= ".($today - 3600)." AND rappel_commercial = '".$user_rappel."' AND rappel_type = 'rdv'");
    if($DB_site2->num_rows($requete)>0){
        while($projet=$DB_site2->fetch_array($requete)){
            $date_suivi=date("d/m H:i",$projet['rappel']);
            $nom=trim($projet['raisonsociale']." ".$projet['nom']." ".$projet['prenom']);
            if($nom=="") $nom="Client ".$projet['userid'];
            $u[]=<<<HTML
            <p>
                $date_suivi :
                <a href="projet.php?projetid=$projet[projetid]" target="_blank">$nom</a>
            </p>
HTML;
        }
        print "<div class=\"rappel\">
        <div class=\"rappeltitle\" style=\"text-decoration:blink;font-size:15px;\">RDV</div>
        <div class=\"rappeltxt\">".implode("",$u)."</div>
        </div>";
    }
}




// Listing des rappels sur COMMANDE. En détails, récupère : 
// - Les rappels en retard (des jours précédents, sans limite de temps)
// - Les rappels du jour actuel (jusqu'à 23h59)
// - Les rappels qui ont été masqués (avec la petite croix), seulement s'ils datent du jour actuel 
$sql = "SELECT *";
$sql .= " FROM suivi_commande";
$sql .= " WHERE pour = '".$user_rappel."'";
$sql .= " AND rappel > 0";
$sql .= " AND rappel < ".$to;
//$sql .= " AND (hide = 0 OR rappel BETWEEN ".$from." AND ".$to.")";
$sql .= " AND hide = 0";
$sql .= " ORDER BY date DESC";
$requete = $DB_site2->query($sql);
while($r = $DB_site2->fetch_array($requete)){

    $affiche_rappel = true;


    if($affiche_rappel){
        
        //Si l'utilisateur en cours est "sav", on regarde si la commande a un SAV prioritaire en cours
        //Si c'est le cas, on écrit le numéro de commande ne gras
        if($user_info['username'] == 'qualite'){
            $sql = "SELECT id";
            $sql .= " FROM sav";
            $sql .= " WHERE facturid = ".$r['facturid'];
            $sql .= " AND fin = 0";
            $sql .= " AND demande_cloture = 0";
            $sql .= " AND sav_priorite = 1";
            $aSAVGras = $DB_site2->query_first($sql);
        }

        //Si la demi-journée est spécifiée sur ce suivi, c'est qu'il n'y a pas d'heure exacte de rappel
        //Pour que, sur une journée, tous les suivis AM/PM ne soient pas mélangés (car pas d'heure spécifiée !), on rajoute 9h (AM) ou 14h (PM) convertis en secondes. 
        //Ca permet d'avoir les suivis AM en 1er, et les suivi PM ensuite.
        if($r['jour'] != 0) $r['rappel'] += $joursh[$r['jour']] * 3600;

        //Calcul de l'heure du rappel
        $datetxt = rappel_heure($r['rappel'],$r['jour'],false,false)." : ";
        
        //Suivi non masqué, on affiche la croix permettant de le faire
        $croix_masque = '';
        if($r['hide'] == 0){
            $croix_masque = "<b class=\"rappelhide\" onclick=\"rappelhide(" . $r['id']. ",'','rappels_cmd')\">X</b>";
        }

        $classP = "type".$r['hide']." ".rappeltype_class($r['rappeltype']);
        $lienTxt = substr("cmd ".$r['facturid'],0,20);

        //Si le type de rappel est indiqué, on affiche la 1ère lettre du type entre parenthèse
        //(i) interne
        //(p) fournisseur (pro)
        //(c) client
        $rappelTypeTxt = '';
        if($r['rappeltype'] != ""){
          $rappelTypeTxt = sprintf("(%s)",substr(rappeltype_txt($r['rappeltype']),0,1));
        } 

        //Construction du tableau
        $rappels2[$r['rappel']] .= ""
        . "<p class=\"" . $classP . "\">"
            . $croix_masque
            . $datetxt
            . "<a href=\"facture.php?action=editer&facturid=" . $r['facturid'] . "\" ".($aSAVGras ? 'style="font-weight: bold;"' : '')." target=\"_blank\">" . $lienTxt . "</a>"
            . "<span>".stripslashes($r['commercial']." ".date("d/m",$r['date']).": ".$r['suivi'])."</span>"
        . "</p>";
    }
}

//Listing des rappels sur commandes de stock
$sql = "SELECT ss.*, sc.destinataire";
$sql .= " FROM suivi_stock ss";
$sql .= " INNER JOIN stock_cmd sc ON (ss.stock_cmd_id = sc.id)";
$sql .= " WHERE pour = '".$user_rappel."'";
$sql .= " AND rappel > 0";
$sql .= " AND rappel < ".$to;
$sql .= " AND (hide = 0 OR rappel BETWEEN ".$from." AND ".$to.")";
$requete = $DB_site2->query($sql);
while($r = $DB_site2->fetch_array($requete)){

    //Calcul de l'heure du rappel
    $datetxt = date("H:i",$r['rappel'])." : ";

    $classP = "type".$r['hide'];
    $lienTxt = "Stock ".substr($r['destinataire'],0,25);

    //Suivi non masqué, on affiche la croix permettant de le faire
    $croix_masque = '';
    if($r['hide'] == 0){
        $croix_masque = "<b class=\"rappelhide\" onclick=\"rappelhide(" . $r['id'] . ",'5','rappels_cmd')\">X</b>";
    }

    $rappels2[$r['rappel']] .= ""
    . "<p class=\"".$classP."\">"
        . $croix_masque 
        . $datetxt 
        . "<a href=\"stock.php?action=cmd&stock_cmd_id=" . $r['stock_cmd_id'] . "\" target=\"_blank\">" . $lienTxt . "</a>"
        . "<span>".stripslashes($r['suivi'])."</span>"
    . "</p>";
}

if(count($rappels2) > 0){

    ksort($rappels2);

    $lastd = "";

    print <<<HTML
    <div id="autorappel" class="rappel">
        <div class="rappeltitle">Rappels commandes</div>
        <div id="rappels_cmd" class="rappeltxt">
HTML;
    
    foreach($rappels2 as $date => $txt){
        if(date("Ymd",$date) != $lastd) print "<div class=\"day\">".date("d/m/Y",$date)."</div>";
        
        echo $txt;
        
        $lastd = date("Ymd",$date);
    }
    
    print <<<HTML
        </div>
    </div>
HTML;
}

$requete=$DB_site2->query("SELECT * FROM suivi_interne_log WHERE pour='$user_rappel' AND rappel>0 AND rappel<$to AND (hide=0 OR rappel BETWEEN $from AND $to)");
while($r=$DB_site2->fetch_array($requete))
  $rappels3[$r['rappel']].=
    "<p class=\"type".$r['hide']."\">"
      .($r['hide']==0?"<span class=\"rappelhide\" onclick=\"rappelhide($r[id],6)\">X</span>":"")
      .date("H:i",$r['rappel'])
      .":<a href=\"task.php?a=showTask&amp;task=$r[task_id]\" onmouseover=\"document.getElementById('rappel$r[id]').style.visibility='visible';\" onmouseout=\"document.getElementById('rappel$r[id]').style.visibility='hidden';\">".substr("SI# ".$r['task_id'],0,20)."</a>
    </p><div id=\"rappel$r[id]\" class=\"popup\">".stripslashes($r['message'])."</div>";
  
    
$contacts=$contacts2=array();
$sqlcom=($aaccess==false || in_array("suivi_commercial.php",$aaccess)?"commercial='' OR":"");
$sql="SELECT * FROM contact WHERE ($sqlcom commercial='$user_rappel') AND date_reponse=0 ORDER BY date";

$requete=$DB_site2->query($sql);
if($DB_site2->num_rows($requete)>0){
    
    while($contact=$DB_site2->fetch_array($requete)){
        if($contact['nom']=="") $contact['nom']=$contact['telephone'];
        $date=date("d/m H:i",$contact['date']);
        $nom=trim($contact['raisonsociale']." ".$contact['nom']." ".$contact['prenom']);
        $link=<<<HTML
            <p>
                $date:
                <a href="contacts.php?contactid=$contact[id]" target="_blank">$nom</a>
                <span>$contact[sujet]</span>
            </p>
HTML;

        if($contact['sujet']=="Rappel immédiat")
            $contacts2[]=$link;
        else
            $contacts[]=$link;
        }
        
        // RAPPELS IMMEDIATS //
        if(count($contacts2)>0)
            print "<div class=\"rappel\" style=\"border-width:2px; border-color:#AA0000;\">
                <div class=\"rappeltitle\" style=\"text-decoration:blink;font-size:15px;\">Rappels imm&eacute;diats</div>
                <div class=\"rappeltxt\" style=\"background-color:#FFCCCC;\">".implode("",$contacts2)."</div>
            </div>";
                
        // DEMANDE CONTACT //
        if(count($contacts)>0)
            print "<div class=\"rappel\">
                <div class=\"contacttitle\">".ucfirst($template->translate("/admin/menu/demande_contact"))."</div>
                <div class=\"rappeltxt\">".implode("",$contacts)."</div>
            </div>";
}

//SELECT count(*),commercial FROM utilisateur u where (select etat from suivi_client where userid=u.userid order by date desc limit 1)=1 and (select valide from facture where userid=u.userid and supprimee='0' order by heurefacture desc limit 1)=0 group by commercial


///DEMANDE CONTACT CAMPAGNE///
$sqlcom=($aaccess==false || in_array("suivi_commercial.php",$aaccess)?"commercial='' OR":"");
$sql="SELECT * FROM contact_leads WHERE ($sqlcom commercial='$user_rappel') AND date_reponse=0 ORDER BY date";
$requete=$DB_site2->query($sql);
if($DB_site2->num_rows($requete)>0){
    print "<div class=\"rappel\">
    <div class=\"contacttitle\">Contacts (via campagne)</div>
    <div class=\"rappeltxt\">";
    while($contact=$DB_site2->fetch_array($requete)){
        $date=date("d/m H:i",$contact['date']);
        $societe=trim($contact['societe']);
        $message=substr($contact['message'],0,20);
        $nom=trim($contact['nom']);
        $prenom=trim($contact['prenom']);
        print "<p>".$date.":
                <a href=\"contacts_leads.php?contactid=".$contact['id']."\" target=\"_blank\">".($societe!=''?$societe:$nom.' '.$prenom)."</a>
                <span>".$message."...</span>
            </p>";
    }
    print "</div></div>";
}
////


//**********************//
// INSCRIPTION DU JOUR //
//********************//
if($user_info['service'] == '1' || $user_info['username']=='philippe'){
    $u=array();
    $today=new datetime();
    $today->settime(0,0,0);
    $requete=$DB_site2->query("	SELECT u.nom,u.prenom,u.raisonsociale,u.dateinscription,u.userid
        FROM utilisateur u
        LEFT JOIN projet USING(userid)
        WHERE `conseils` = 'oui' AND projetid IS NULL AND (SELECT COUNT(*) FROM projet_relation WHERE userid=u.userid)=0 AND (SELECT COUNT(*) FROM facture WHERE (valide IN (1,5,7,8) OR moyenid>0) AND userid=u.userid)=0 AND (SELECT COUNT(*) FROM suivi_client WHERE userid=u.userid AND etat=0)=0
        ORDER BY u.userid desc LIMIT 20");
        
    if($DB_site2->num_rows($requete)>0){
        while($utilisateur=$DB_site2->fetch_array($requete)){
            $d=new datetime($utilisateur['dateinscription']);
            $ddate=$d<$today ? $d->format("d/m") : $d->format("H:i");
            $nom=trim($utilisateur['raisonsociale']." ".$utilisateur['nom']." ".$utilisateur['prenom']);
            if($nom=="") $nom="Client ".$utilisateur['userid'];
            $u[]=<<<HTML
            <p>
                $ddate :
                <a href="utilisateur.php?action=editer&amp;userid=$utilisateur[userid]" target="_blank">$nom</a>
            </p>
HTML;
        }
        print "<div class=\"rappel\">
        <a href=\"utilisateur.php?noprojet=1\" class=\"registertitle\" target=\"_blank\">".ucfirst($template->translate("/admin/menu/inscriptions"))."</a>
        <div class=\"rappeltxt\">".implode("",$u)."</div>
        </div>";
    }
}

$projets=new projets();

$norappel=array();
foreach($projets->norappel($user_rappel,$projets->encours) as $projet){
    $user=$projet->users("client");
    $nom=trim($user[0]->raisonsociale." ".$user[0]->nom." ".$user[0]->prenom);
    
    $class="";
    $datecmd=$projet->datecmd();
    if(!is_null($datecmd)){
        if($datecmd->gettimestamp()<time()) $class="red";
        $date="cmd:<i>".$datecmd->format("d/m/Y")."</i>";
    }else{
        $date="sans échéance";
    }
    
    $key=$projet->date->gettimestamp().$projet->projetid;
    $norappel[$key]=<<<HTML
        <p class="$class">
            <a href="projet.php?projetid=$projet->projetid" target="_blank">$nom</a>
            <span>$projet->pjt $date $projet->etat<br>$projet->libelle</span>
        </p>
HTML;
}

if(count($norappel) > 0){
    ksort($norappel);
    
    echo ""
    . "<div id=\"autorappel\" class=\"rappel\">"
        . "<div class=\"rappeltitle\" style=\"background:#52b6e8;\"><a href=\"projets.php?order=&sensorder=&action=search&commercial=".$user_rappel."&projet_etatid=".implode(",",$projets->encours)."&projet_typeid=&rappel=sans_rappel&until=0&rappel_com=\" title=\"Voir les projets sans rappel\">Projets sans rappel</a></div>"
        . "<div id=\"rappeltxt\" class=\"rappeltxt\">"
        .implode("",$norappel)
        ."</div>"
    . "</div>";
}

$achiffrer=array();
foreach($projets->search($user_rappel,3) as $projet){
    $user=$projet->users("client");
    $nom=trim($user[0]->raisonsociale." ".$user[0]->nom." ".$user[0]->prenom);
    $datecmd=$projet->datecmd();
    if(!is_null($datecmd)){
        $color="";
        if($datecmd->gettimestamp()<time()) $class="red";
        $date="cmd:<i>".$datecmd->format("d/m/Y")."</i>";
    }else
        $date="sans échéance";
    
    $achiffrer[$projet->projetid]=<<<HTML
        <p>
            <a href="projet.php?projetid=$projet->projetid" target="_blank">$nom</a>
            <span>$projet->pjt $date $projet->etat<br>$projet->libelle</span>
        </p>
HTML;
}

if(count($achiffrer) > 0){
    ksort($achiffrer);
    
    echo ""
    . "<div id=\"autorappel\" class=\"rappel\">"
        . "<div class=\"rappeltitle\" style=\"background:#52b6e8;\"><a href=\"projets.php?order=&sensorder=&action=search&commercial=".$user_rappel."&projet_etatid=3&projet_typeid=&rappel=&until=0&rappel_com=\" title=\"Voir les projets à chiffrer\">Projets à chiffrer</a></div>"
        . "<div id=\"rappeltxt\" class=\"rappeltxt\">"
        .implode("",$achiffrer)
        ."</div>"
    . "</div>";
}

  //*********************************//
 // RAPPELS CLIENTS DU JOUR PROJETS //
//*********************************//
$today=date("Ymd");
$rappelsjour=$rappelsretard=$rappelsretard15=array();
$jour7=new datetime("7 days ago");
$jour15=new datetime("15 days ago");

foreach($projets->search(null,$projets->encours,null,$fromdisplay,$to,$user_rappel) as $projet){
    $datetxt="";
    if($projet->rappel->format("G")>0) $datetxt=$projet->rappel->format("H:i");
    
    $user=$projet->users("client");
    $nom=trim($user[0]->raisonsociale." ".$user[0]->nom." ".$user[0]->prenom);
    
    foreach($projet->rappels() as $rappel){
        //var_dump($rappel,$projet->projetid);
        if($rappel['ts']>=$fromdisplay && $rappel['ts']<=$to){
            $class="";
            $datecmd=$projet->datecmd();
            if(!is_null($datecmd)){
                $color="";
                if($datecmd->gettimestamp()<time()) $class="red";
                $date="cmd:<i>".$datecmd->format("d/m/Y")."</i>";
            }else
                $date="sans échéance";
            
            if(date("Ymd",$rappel['ts'])==$today ){
                $rappelsjour[$rappel['ts'].$projet->projetid]=<<<HTML
                <p class="$class">
                    $datetxt
                    <a href="projet.php?projetid=$projet->projetid" target="_blank">$nom</a>
                    <span>$projet->pjt $date $projet->etat<br>$projet->libelle</span>
                </p>
HTML;
            }else{
                $date=date("d/m",$rappel['ts']);
                $retard15=$rappel['date']<$jour15?"15":"";
                ${"rappelsretard".$retard15}[$rappel['ts'].$projet->projetid]=<<<HTML
                <p class="$class">
                    $date:
                    <a href="projet.php?projetid=$projet->projetid" target="_blank">$nom</a>
                    <span>$projet->pjt $date $projet->etat<br>$projet->libelle</span>
                </p>
HTML;
            }
        }
    }
}

if(count($rappelsjour)+count($rappelsretard) > 0){
    
    echo ""
    . "<div id=\"autorappel\" class=\"rappel\">"
        . "<div class=\"rappeltitle\"><a href=\"projets.php?order=&sensorder=&action=search&commercial=&projet_etatid=".implode(",",$projets->encours)."&projet_typeid=&rappel=rappels_jour&until=0&rappel_com=".$user_info['username']."\" title=\"Voir les rappels du jour\" target=\"_blank\">Rappels projets</a></div>"
        . "<div id=\"rappeltxt\" class=\"rappeltxt\">";
    
    if(count($rappelsjour)>0){
        ksort($rappelsjour);
        print "<div class=\"day\"><a href=\"projets.php?order=&sensorder=&action=search&commercial=&projet_etatid=".implode(",",$projets->encours)."&projet_typeid=&rappel=rappels_jour&until=0&rappel_com=".$user_info['username']."\" title=\"Voir les rappels du jour\" target=\"_blank\">Rappels du jour</a></div>"
        .implode("",$rappelsjour);
    }
    
    if(count($rappelsretard)>0){
        krsort($rappelsretard);
        print "<div class=\"day\"><a href=\"projets.php?order=&sensorder=&action=search&commercial=&projet_etatid=".implode(",",$projets->encours)."&projet_typeid=&rappel=rappels_retard&until=0&rappel_com=".$user_info['username']."\" title=\"Voir les rappels en retard\" target=\"_blank\">Rappels en retard</a></div>"
        .implode("",array_slice($rappelsretard,0,20));
    }
    
    if(count($rappelsretard15)>0){
        krsort($rappelsretard15);
        print "<div class=\"day\"><a href=\"projets.php?order=&sensorder=&action=search&commercial=&projet_etatid=".implode(",",$projets->encours)."&projet_typeid=&rappel=rappels_retard&until=0&rappel_com=".$user_info['username']."\" title=\"Voir les rappels en retard\" target=\"_blank\">Rappels retard > 15j</a></div>"
        .implode("",array_slice($rappelsretard15,0,20));
    }
    
    echo "</div>"
    . "</div>";
}
//********************************//
// RAPPELS SUIVI INTERNE DU JOUR //
//******************************//
ksort($rappels3);

if(count($rappels3)>0) print "<div id=\"autorappel\" class=\"rappel\">
<div class=\"rappeltitle\" style=\"background-color:#00b\">".ucfirst($template->translate("/admin/menu/rappels"))." suivi interne</div>
<div id=\"rappeltxt\" class=\"rappeltxt\">";
foreach($rappels3 as $date=>$txt){
  if(date("d",$date)!=$lastd) print "<p class=\"day\">".date("d/m/Y",$date)."</p>";
  print $txt;
  $lastd=date("d",$date);
}
print "</div>
</div>";


if($alertid>0)
  $alert="<p class=\"title\"><img src=\"../images/templates/warning.jpg\"> Rappel</p>".
  $alert.
  "<p><a href=\"javascript:rappelhide($alertid,2);alerthide();\">Supprimer le rappel</a> <!--<a href=\"\">D&eacute;caler le rappel</a>--></p>";

print "</div>
</div>
<div id=\"alertbg\"></div>
<div id=\"alert\">$alert</div>";


// $tr = new TaskReminder($DB_site2, $user_rappel);
// $tr->show('Suivi interne: ce jour', $tr->taskList(false));
// $tr->show('Suivi interne: autres', $tr->taskList(false));

?>
