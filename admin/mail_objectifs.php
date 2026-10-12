<?php
ini_set('display_errors',1);

include_once('host_config_admin.php');
include_once($rootpath."class/email.php");
include_once($rootpath."admin/fonctions.php");

$nostat=1;

$DB_site= clone $DB_site2;

$year=date("Y");
$mois=array(1=>"janvier",2=>"février",3=>"mars",4=>"avril",5=>"mai",6=>"juin",7=>"juillet",8=>"août",9=>"septembre",10=>"octobre",11=>"novembre",12=>"décembre");
$semaine=array(0=>"dimanche",1=>"lundi",2=>"mardi",3=>"mercredi",4=>"jeudi",5=>"vendredi",6=>"samedi",7=>"dimanche");


$trimestre=ceil(date("n")/3);
$trim_mois_debut=($trimestre-1)*3+1;
$trim_mois_fin=$trim_mois_debut+2;
$nbjours=cal_days_in_month(CAL_GREGORIAN,$trim_mois_fin,$year);
$debut_trimestre=mktime(0,0,0,$trim_mois_debut,1,$year);
$fin_trimestre=mktime(23,59,59,$trim_mois_fin,$nbjours,$year);
$tdmois=$mois[date("n")]." ".$year;
$tddate=$semaine[date("w")]." ".date("d")." ".$tdmois;


$commercials=$DB_site->query(" SELECT p.username,p.nom_contact,p.mail_contact FROM parametresgeneraux p WHERE (SELECT objectif_ca FROM objectif_commercial WHERE username=p.username AND annee=YEAR(NOW()) AND mois=MONTH(NOW()))>0 AND actif=1");
while($commercial=$DB_site->fetch_array($commercials)){
    $abs=$DB_site->query_first("SELECT COUNT(*) FROM absence WHERE username='".$commercial['username']."' AND '".date("Y-m-d")."' BETWEEN datedebut AND datefin");
    if($abs[0]>0) continue;

    $mail=new email();
    $mail->expediteur="contact@mon-espace-cuisson.com";
    $mail->expname="Ma Cave à Vin";
    $mail->destinataire=$commercial['mail_contact'];
    $mail->to="pr@mon-espace-cuisson.com";
    $mail->subject="Information commercial ".$commercial['nom_contact'];
    
    $objectif_commercial=array();
    $objectifs_sql=$DB_site->query("SELECT * FROM objectif_commercial WHERE username='".$commercial['username']."' AND annee='".$year."'");
    while($objectif=$DB_site->fetch_array($objectifs_sql)){
        $objectif_commercial[$objectif[mois]]=$objectif[objectif_ca];
    }
    $ca=ca($year,null,$commercial['username']);
    $objectif_trimestre=$objectif_mois=$objectif_jour=0;
    $objectif_annee_encours=$objectif_trimestre_encours=$objectif_mois_encours=0;
    for($m=$trim_mois_debut;$m<=$trim_mois_fin;$m++){
        $nbjoursmois=cal_days_in_month(CAL_GREGORIAN,$m,$year);
        $jourstravail=getJours($year."-".$m."-1",$year."-".$m."-".$nbjoursmois,$commercial['username']);
        $objectif_trimestre+=$jourstravail*$objectif_commercial[$m];
        $objectif_annee_encours+=$jourstravail*$objectif_commercial[$m];

        if($m<date("n"))
            $objectif_trimestre_encours+=$jourstravail*$objectif_commercial[$m];
        if($m==date("n")){
            $objectif_mois=$objectif_commercial[$m]*$jourstravail;
            $objectif_jour=$objectif_commercial[$m];
        }
    }

    for($m=1;$m<date("n");$m++){
        $nbjoursmois=cal_days_in_month(CAL_GREGORIAN,$m,$year);
        $jourstravail=getJours($year."-".$m."-1",$year."-".$m."-".$nbjoursmois,$commercial['username']);
        
    }
    
    $jourdebutmois=0;
    // si on est le dernier jour du mois, à partir de 20h, on affiche les résultats du jour actuel
    if(date("j")==cal_days_in_month(CAL_GREGORIAN,$m,$year) && date('h:i:s') > '19:59:59' ) {
        $jour_en_cours=getJours($year."-".date("n")."-1",$year."-".date("n")."-".date("j"),$commercial['username']);
    }
    // sinon, on affiche les résultats du jour -1
    else {
        $jour_en_cours=getJours($year."-".date("n")."-1",$year."-".date("n")."-".date("j"),$commercial['username'])-1;
    }

    $objectif_mois_encours=$jour_en_cours*$objectif_commercial[date("n")];
    $objectif_trimestre_encours+=$objectif_mois_encours;

    $ca=ca($year,null,$commercial['username']);
    $canm1=ca($year-1,mktime(0,0,0,date("n"),date("j"),$year-1),$commercial['username']);

    $camois=$ca['ca'][date("n")];

    $caannee=$catrimestre=$caanneem1=$catrimestrem1=$objectif_trimestreencours=$objectif_anneeencours=0;
    for($mois=$trim_mois_debut;$mois<=$trim_mois_fin;$mois++){
        if($mois == date("n")) {
            $objectif_trimestreencours+=$jour_en_cours*$objectif_commercial[$mois];
        }
        elseif($mois<date("n")) {
            $nbjoursmois=cal_days_in_month(CAL_GREGORIAN,$mois,$year);
            $jourstravail=getJours($year."-".$mois."-1",$year."-".$mois."-".$nbjoursmois,$commercial['username']);
            $objectif_trimestreencours+=$jourstravail*$objectif_commercial[$mois];
        }
        if($mois<=date("n")){
            $catrimestre+=$ca['ca'][$mois];
            $catrimestrem1+=$canm1['ca'][$mois];
        }
    }
    foreach($ca['ca'] as $m=>$data){
        $caannee+=$data;
        $caanneem1+=$canm1['ca'][$m];
    }

    for($m=1;$m<13;$m++){
        if($m == date("n")) {
            $jour_en_cours=getJours($year."-".date("n")."-1",$year."-".date("n")."-".date("j"),$commercial['username']);
            $objectif_anneeencours+=$jour_en_cours*$objectif_commercial[date("n")];
        }
        elseif($m<date("n")){
            $nbjoursmois=cal_days_in_month(CAL_GREGORIAN,$m,$year);
            $jourstravail=getJours($year."-".$m."-1",$year."-".$m."-".$nbjoursmois,$commercial['username']);
            $objectif_anneeencours+=$jourstravail*$objectif_commercial[$m];
        }
    }

    // si premier jour du mois on met les résultats à 0, sinon ça prendrai les résultats du mois précédent
    if(date("j")==1) {
        $diffmois=0;
    }
    else {
        $diffmois=$camois-$objectif_mois_encours;
    }
    

    if($objectif_mois_encours>0)
        $pcdiffmois=number_format((100*$diffmois/$objectif_mois_encours),1,',',' ');
    else
        $pcdiffmois=0;

    $difftrimestre=$catrimestre-$objectif_trimestreencours;

    if($objectif_trimestre_encours>0)
        $pcdifftrimestre=number_format((100*$difftrimestre/$objectif_trimestreencours),1,',',' ');
    else
        $pcdifftrimestre=0;

    $diffannee=$caannee-$objectif_anneeencours;

    if($objectif_annee_encours>0)
        $pcdiffannee=number_format((100*$diffannee/$objectif_anneeencours),1,',',' ');
    else
        $pcdiffannee=0;


    $difftrimestrem1=$catrimestre-$catrimestrem1;
    if($catrimestrem1>0)
        $pcdifftrimestrem1=number_format((100*$difftrimestrem1/$catrimestrem1),1,',',' ');
    else
        $pcdifftrimestrem1=0;
    
    $diffanneem1=$caannee-$caanneem1;
    if($caanneem1>0)
        $pcdiffanneem1=number_format((100*$diffanneem1/$caanneem1),1,',',' ');
    else
        $pcdiffanneem1=0;
    
    $camois=number_format($camois,0,',',' ');
    $caannee=number_format($caannee,0,',',' ');
    $catrimestre=number_format($catrimestre,0,',',' ');
    $diffmoiscolor=$diffmois<0?"red":"green";
    $diffmoissign=($diffmois>0?"+":"");
    $diffmois=number_format($diffmois,0,',',' ');
    $difftrimestrecolor=$difftrimestre<0?"red":"green";
    $difftrimestresign=$difftrimestre>0?"+":"";
    $difftrimestre=number_format($difftrimestre,0,',',' ');
    $diffanneecolor=$diffannee<0?"red":"green";
    $diffanneesign=$diffannee>0?"+":"";
    $diffannee=number_format($diffannee,0,',',' ');
    $difftrimestrem1color=$difftrimestrem1<0?"red":"green";
    $difftrimestrem1sign=$difftrimestrem1>0?"+":"";
    $difftrimestrem1=number_format($difftrimestrem1,0,',',' ');
    $diffanneem1color=$diffanneem1<0?"red":"green";
    $diffanneem1sign=$diffanneem1>0?"+":"";
    $diffanneem1=number_format($diffanneem1,0,',',' ');
    
    $mail->body=<<<HTML
    <style>
        table { margin:10px 0; width:600px; border:solid 1px black; border-collapse:collapse; }
        td { border:solid 1px black; padding:2px 5px; }
        .num { text-align:right; }
        .center { text-align:center; }
    </style>
    <table>
        <tr>
            <td>Trimestre</td>
            <td class="center">$trimestre</td>
        </tr>
        <tr>
            <td>Objectif trimestre</td>
            <td class="num">$objectif_trimestre &euro;</td>
        </tr>
        <tr>
            <td>Mois</td>
            <td class="center">$tdmois</td>
        </tr>
        <tr>
            <td>Objectif journalier</td>
            <td class="num">$objectif_jour &euro;</td>
        </tr>
        <tr>
            <td>Objectif du mois</td>
            <td class="num">$objectif_mois &euro;</td>
        </tr>
    </table>
    <br>
    <table>
        <tr>
            <td>Date du jour</td>
            <td class="center">$tddate</td>
        </tr>
    </table>
    <br>
    <table>
        <tr>
            <td>CA mois</td>
            <td class="num">$camois &euro;</td>
        </tr>
        <tr>
            <td>CA trimestre</td>
            <td class="num">$catrimestre &euro;</td>
        </tr>
        <tr>
            <td>CA annuel</td>
            <td class="num">$caannee &euro;</td>
        </tr>
        <tr>
            <td>Avance/retard /objectif du mois</td>
            <td class="num" style="color:$diffmoiscolor;">$diffmoissign$diffmois &euro;</td>
            <td class="num" style="color:$diffmoiscolor;">$diffmoissign$pcdiffmois%</td>
        </tr>
        <tr>
            <td>Avance/retard /objectif du trimestre</td>
            <td class="num" style="color:$difftrimestrecolor;">$difftrimestresign$difftrimestre &euro;</td>
            <td class="num" style="color:$difftrimestrecolor;">$difftrimestresign$pcdifftrimestre%</td>
        </tr>
        <tr>
            <td>Avance/retard /objectif annuel</td>
            <td class="num" style="color:$diffanneecolor;">$diffanneesign$diffannee &euro;</td>
            <td class="num" style="color:$diffanneecolor;">$diffanneesign$pcdiffannee%</td>
        </tr>
        <tr>
            <td>Avance/retard /Enr N-1 du trimestre</td>
            <td class="num" style="color:$difftrimestrem1color;">$difftrimestrem1sign$difftrimestrem1 &euro;</td>
            <td class="num" style="color:$difftrimestrem1color;">$difftrimestrem1sign$pcdifftrimestrem1%</td>
        </tr>
        <tr>
            <td>Avance/retard /Enr N-1 annuel</td>
            <td class="num" style="color:$diffanneem1color;">$diffanneem1sign$diffanneem1 &euro;</td>
            <td class="num" style="color:$diffanneem1color;">$diffanneem1sign$pcdiffanneem1%</td>
        </tr>
    </table>
    <p>Voir vos stats en détail, cliquez ici : <a href="https://www.ma-cave-a-vin.fr/admin/stats_objectifs_commerciaux.php?username=$commercial[username]">www.ma-cave-a-vin.fr/admin/stats_objectifs_commerciaux.php?username=$commercial[username]</a></p>
HTML;

//     print "<h1>".$commercial['username']."</h1>";
//     print $mail->body;
    $mail->sendmail();
}


function ca($annee,$to=null,$username=""){
    global $DB_site2;
    
    if(is_null($to)) $to = mktime(23,59,59,12,31,$annee);
    $ca = $nb = $remise = $marge = $attente = array();
    $totalvente=0;
    $sql = "SELECT f.facturid, f.heurefacture, f.provenance,
            SUM((lf.prix * lf.qte) / (1 + (lf.tva / 100))) as 'prixHT',
            f.prixport / (1 + f.tvaport / 100) as 'portHT',
            CASE WHEN (SELECT code_caviste_id FROM code_caviste WHERE code = f.cadeauid) IS NULL THEN (SUM((lf.prix * lf.qte) / (1 + (lf.tva / 100))) + (f.prixport / (1 + f.tvaport / 100))) * (f.montantcadeau / (SUM(lf.prix * lf.qte) + f.prixport)) ELSE 0 END as remiseHT";
    $sql .= " FROM facture f";
    $sql .= " INNER JOIN lignefacture lf ON (lf.factureid = f.facturid)";
    $sql .= " WHERE f.heurefacture BETWEEN ".mktime(0,0,0,1,1,$annee)." AND ".$to;
    $sql .= " AND f.valide IN (1,5,7,8)";
    $sql .= " AND f.provenanceid = ''";
    $sql .= " AND f.commercial_attribution ='".$username."' ";
    $sql .= " GROUP BY f.facturid";

    $req = $DB_site2->query($sql);
    while($facture = $DB_site2->fetch_array($req)){
        $month = date("n",$facture['heurefacture']);
        // echo "<br><br>mois de ".$month."<br>";
        $totalvente = $facture['prixHT'] + $facture['portHT'] - $facture['remiseHT'];
        $ca[$month] += $totalvente;
        $nb[$month]++;
    }

    /*  A DECOMMENTER POUR AVOIR DES CALCULS AVEC LE CA PREVISIONNEL (les commandes en attente de règlement)
    // ici uniquement les commandes en attente de règlement
    $sql = "SELECT f.facturid, f.heurefacture, f.provenance,
            SUM((lf.prix * lf.qte) / (1 + (lf.tva / 100))) as 'prixHT',
            f.prixport / (1 + f.tvaport / 100) as 'portHT',
            CASE WHEN (SELECT code_caviste_id FROM code_caviste WHERE code = f.cadeauid) IS NULL THEN (SUM((lf.prix * lf.qte) / (1 + (lf.tva / 100))) + (f.prixport / (1 + f.tvaport / 100))) * (f.montantcadeau / (SUM(lf.prix * lf.qte) + f.prixport)) ELSE 0 END as remiseHT";
    $sql .= " FROM facture f";
    $sql .= " INNER JOIN lignefacture lf ON (lf.factureid = f.facturid)";
    $sql .= " WHERE f.heurefacture BETWEEN ".mktime(0,0,0,1,1,$annee)." AND ".$to;
    $sql .= " AND f.valide = 0";
    $sql .= " AND f.moyenid in (2,4,5,14,17,18,99)";
    $sql .= " AND f.commercial_attribution ='".$username."' ";
    $sql .= " GROUP BY f.facturid";
    $req = $DB_site2->query($sql);
    while($facture = $DB_site2->fetch_array($req)){
        $month = date("n",$facture['heurefacture']);
        $totalattente = $facture['prixHT'] + $facture['portHT'] - $facture['remiseHT'];
        $ca[$month] += $totalattente;
    }*/
    
    return array(
        "ca" => $ca,
        "nb" => $nb
    );
}
