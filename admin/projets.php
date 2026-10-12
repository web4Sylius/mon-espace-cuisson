<?php
require_once("admin_global.php");
include_once("./header.php");
include_once("./menu.php");

include_once("../class/projet.class.php");
// $projet=new projet(51163);
// var_dump($projet->rappels());
$projets=new projets();

$action  = isset($_GET['action']) ? $_GET['action'] : '';
$order  = isset($_GET['order']) ? $_GET['order'] : 'rappel ASC';

$commercial  = isset($_GET['commercial']) ? $_GET['commercial'] : '';
$projet_etatid  = isset($_GET['projet_etatid']) ? $_GET['projet_etatid'] : null;
$projet_typeid  = isset($_GET['projet_typeid']) ? $_GET['projet_typeid'] : '';
$rappel  = isset($_GET['rappel']) ? $_GET['rappel'] : 'rappels_jour';
$rappel_com  = isset($_GET['rappel_com']) ? $_GET['rappel_com'] : '';
$nobudget=isset($_GET['nobudget']);
$noecheance=isset($_GET['noecheance']);
$nocmddelai=isset($_GET['nocmddelai']);
$srctxt=!empty($_GET['src']) ? $_GET['src'] : null;
$fromecheance=!empty($_GET['fromecheance']) ? $_GET['fromecheance'] : null;
$toecheance=!empty($_GET['toecheance']) ? $_GET['toecheance'] : null;
$dateecheance=null;
if(!is_null($fromecheance) && !is_null($toecheance)) $dateecheance=array($fromecheance,$toecheance);

$liste_etats_encours=array(1,2,3,4,7,8,9);
$liste_etats_clos=array(5,6);


?>
<h1>Projets :</h1>

<?php include("projets_src.php"); ?>

<script type="text/javascript">
    function setorder(order,sens){
        document.getElementsByName("order")[0].value=order;
        document.getElementsByName("sensorder")[0].value=sens;
        document.forms['search'].submit();
    }
    function changerappel(){
        var periode=document.getElementsByName('rappel')[0].value;
        var displayuntil="none";
        switch(periode){
            case "rappels_jour":
            displayuntil="inline";
            document.getElementsByName('until')[0].value=0;
            break;
            
            case "rappels_futur":
            displayuntil="inline";
            document.getElementsByName('until')[0].querySelector('option:last-child').selected=true;
            break;
        }
        document.getElementById("rappel_until").style.display=displayuntil;
    }
</script>

<?php

$stats=$projets->stats($commercial);

$urlencours=rawurlencode(implode(",",$projets->encours));
$columns=array(2,3,8,7,4);
$totalcol=array();
$qtecol=array();
$tdtypebudget="";
foreach($projets->budget_types() as $btid=>$libelle){
    $td="";
    $qtetotal=0;
    $total=0;
    foreach($columns as $col){
        $qte=0;
        if(!isset($totalcol[$col])) $totalcol[$col]=0;
        if(!isset($qtecol[$col])) $qtecol[$col]=0;
        if(isset($stats['budget'][$btid][$col])){
            $qte=$stats['budget'][$btid][$col]['qte'];
            $qtecol[$col]+=$qte;
            $total+=$stats['budget'][$btid][$col]['total'];
            $totalcol[$col]+=$stats['budget'][$btid][$col]['total'];
        }
        $qtetotal+=$qte;
        $td.="<td align=\"center\" onclick=\"document.location='projets.php?action=search&commercial=$commercial&projet_etatid=".$col."&projet_typeid=".$btid."&rappel=&rappel_com=';\">".$qte."</td>";
    }
    
    $total=number_format($total,0,',',' ');
    
    $sansbudget=isset($stats['infos'][$btid]['budget']) ? $stats['infos'][$btid]['budget'] : 0;
    if(!isset($qtecol2['budget'])) $qtecol2['budget']=0;
    $qtecol2['budget']+=$sansbudget;
    $sansecheance=isset($stats['infos'][$btid]['echeance']) ? $stats['infos'][$btid]['echeance'] : 0;
    if(!isset($qtecol2['echeance'])) $qtecol2['echeance']=0;
    $qtecol2['echeance']+=$sansecheance;
    $sansdelai=isset($stats['infos'][$btid]['delai_cmd']) ? $stats['infos'][$btid]['delai_cmd'] : 0;
    if(!isset($qtecol2['delai'])) $qtecol2['delai']=0;
    $qtecol2['delai']+=$sansdelai;
    $sansrappel=isset($stats['infos'][$btid]['rappel']) ? $stats['infos'][$btid]['rappel'] : 0;
    if(!isset($qtecol2['rappel'])) $qtecol2['rappel']=0;
    $qtecol2['rappel']+=$sansrappel;
    
    $tablebudget.=<<<HTML
    <tr>
        <td>$libelle</td>
        <td align="center" onclick="document.location='projets.php?action=search&commercial=$commercial&projet_etatid=$urlencours&projet_typeid=$btid&rappel=&rappel_com=';">$qtetotal</td>
        $td
        <td align="right" onclick="document.location='projets.php?action=search&commercial=$commercial&projet_etatid=$urlencours&projet_typeid=$btid&rappel=&rappel_com=';">$total&euro;</td>
        <td align="center" onclick="document.location='projets.php?action=search&commercial=$commercial&projet_etatid=$urlencours&projet_typeid=$btid&rappel=&rappel_com=&nobudget=1';">$sansbudget</td>
        <td align="center" onclick="document.location='projets.php?action=search&commercial=$commercial&projet_etatid=$urlencours&projet_typeid=$btid&rappel=&rappel_com=&noecheance=1';">$sansecheance</td>
        <td align="center" onclick="document.location='projets.php?action=search&commercial=$commercial&projet_etatid=$urlencours&projet_typeid=$btid&rappel=&rappel_com=&nocmddelai=1';">$sansdelai</td>
        <td align="center" onclick="document.location='projets.php?action=search&commercial=$commercial&projet_etatid=$urlencours&projet_typeid=$btid&rappel=sans_rappel&rappel_com=';">$sansrappel</td>
    </tr>
HTML;

    if($btid>0)
        $tdtypebudget.="<td colspan=\"2\" class=\"entete\">".$libelle."</td>";
}

$td="";
$tdtotal="";
$tdqte="";
foreach($columns as $col){
    $td.="<td class=\"entete\">".$projets->etats[$col]."</td>";
    $tdqte.="<td align=\"center\" onclick=\"document.location='projets.php?action=search&commercial=$commercial&projet_etatid=$col&projet_typeid=&rappel=&rappel_com=';\">".$qtecol[$col]."</td>";
    $tdtotal.="<td align=\"right\">".number_format($totalcol[$col],0,',','&nbsp;')."&euro;</td>";
}
$total=number_format(array_sum($totalcol),0,',','&nbsp;');
$tdtotalqte=array_sum($qtecol);

$maxmonth=7;
$periodes=array("passe"=>"En retard","semaine"=>"Semaine en cours","semaineprochaine"=>"Semaine prochaine",date("Y-m")=>"Mois en cours");
$echeanceurl=array();
$echeanceurl["passe"]="&fromecheance=946681200&toecheance=".date_create("yesterday 23:59:59")->gettimestamp();
$echeanceurl["semaine"]="&fromecheance=".date_create((date("N")==1?"today":"last monday")." 0:0:0")->gettimestamp()."&toecheance=".date_create("next sunday 23:59:59")->gettimestamp();
$echeanceurl["semaineprochaine"]="&fromecheance=".date_create("monday next week 0:0:0")->gettimestamp()."&toecheance=".date_create("sunday next week 23:59:59")->gettimestamp();
$echeanceurl[date("Y-m")]="&fromecheance=".date_create("first day of this month 0:0:0")->gettimestamp()."&toecheance=".date_create("last day of this month 23:59:59")->gettimestamp();
for($i=1;$i<=$maxmonth;$i++){
    $d=new datetime("+".$i." month");
    $d->modify("first day of this month 0:0:0");
    $periodes[$d->format("Y-m")]=$d->format("m/Y");
    $d2=clone $d;
    $d2->modify("last day of this month 23:59:59");
    $echeanceurl[$d->format("Y-m")]="&fromecheance=".$d->gettimestamp()."&toecheance=".$d2->gettimestamp();
}
$periodes["futur"]="Futur";
$periodes["sans"]="Sans échéance";
$echeanceurl["sans"]="&noecheance=1";

$minmonth=new datetime("+".($maxmonth+1)." month");
$minmonth->modify("first day of this month 0:0:0");
$echeanceurl["futur"]="&fromecheance=".$minmonth->gettimestamp()."&toecheance=".date_create("+1000 days")->gettimestamp();
$futur=array();
foreach(preg_grep("/^\d+-\d+$/",array_keys($stats['delai'])) as $Ym){
    $d=new datetime($Ym."-01");
    if($d>=$minmonth){
        foreach($stats['delai'][$Ym] as $btid=>$bt){
            if(!isset($futur[$btid])) $futur[$btid]=array("qte"=>0,"total"=>0);
            $futur[$btid]['qte']+=$bt['qte'];
            $futur[$btid]['total']+=$bt['total'];
        }
            
    }
}
$stats['delai']['futur']=$futur;

$tableecheance="";
foreach($periodes as $key=>$libelle){
    $tableecheance.="<tr>
        <td>$libelle</td>";
    
    $totalligne=0;
    $qteligne=0;
    foreach($projets->budget_types() as $btid=>$lib){
        if($btid>0){
            $qte=$total=0;
            if(isset($stats['delai'][$key][$btid])){
                $qte=$stats['delai'][$key][$btid]['qte'];
                $total=$stats['delai'][$key][$btid]['total'];
            }
            $url="projets.php?action=search&commercial=".$commercial."&projet_etatid=".$urlencours."&projet_typeid=".$btid."&rappel=&rappel_com=".$echeanceurl[$key];
            
            $tableecheance.="<td align=\"center\" onclick=\"document.location='".$url."'\">".$qte."</td>
            <td align=\"right\" onclick=\"document.location='".$url."'\">".number_format($total,0,","," ")."€</td>";
            $totalligne+=$total;
            $qteligne+=$qte;
        }
    }

    $url="projets.php?action=search&commercial=".$commercial."&projet_etatid=".$urlencours."&projet_typeid=&rappel=&rappel_com=".$echeanceurl[$key];
    $tableecheance.="<td align=\"center\" onclick=\"document.location='".$url."'\">".$qteligne."</td>
    <td align=\"right\" onclick=\"document.location='".$url."'\">".number_format($totalligne,0,","," ")."€</td>
    </tr>";
}

print <<<HTML
    <table class="table_alternate_color" style="width:1200px;">
        <tr>
            <td class="entete reducer" onclick="this.closest('table').classList.toggle('reduce')">Projets en cours $commercial</td>
            <td class="entete">Qté totale</td>
            $td
            <td class="entete">Budget</td>
            <td class="entete">Sans budget</td>
            <td class="entete">Sans échéance</td>
            <td class="entete">Sans délai</td>
            <td class="entete">Sans rappel</td>
        </tr>
        <tr>
            <td>Nbre de projet en cours</td>
            <td align="center" onclick="document.location='projets.php?action=search&commercial=$commercial&projet_etatid=$urlencours&projet_typeid=&rappel=&rappel_com=';">$tdtotalqte</td>
            $tdqte
            <td></td>
            <td align="center" onclick="document.location='projets.php?action=search&commercial=$commercial&projet_etatid=$urlencours&projet_typeid=&rappel=&rappel_com=&nobudget=1';">$qtecol2[budget]</td>
            <td align="center" onclick="document.location='projets.php?action=search&commercial=$commercial&projet_etatid=$urlencours&projet_typeid=&rappel=&rappel_com=&noecheance=1';">$qtecol2[echeance]</td>
            <td align="center" onclick="document.location='projets.php?action=search&commercial=$commercial&projet_etatid=$urlencours&projet_typeid=&rappel=&rappel_com=&nocmddelai=1';">$qtecol2[delai]</td>
            <td align="center" onclick="document.location='projets.php?action=search&commercial=$commercial&projet_etatid=$urlencours&projet_typeid=&rappel=sans_rappel&rappel_com=';">$qtecol2[rappel]</td>
        </tr>
        $tablebudget
        <tr>
            <td>Budget</td>
            <td></td>
            $tdtotal
            <td align="right">$total&euro;</td>
        </tr>
    </table>
    
    <br>
    <table class="table_alternate_color reduce" style="width:1200px;">
        <tr>
            <td class="entete reducer" onclick="this.closest('table').classList.toggle('reduce')">Echéance passage commande</td>
            $tdtypebudget
            <td class="entete">Qté totale</td>
            <td class="entete">Budget</td>
        </tr>
        $tableecheance
    </table>
HTML;

if($action=="search"){
    $function="search";
    switch($_GET['rappel']){
        
        case "rappels_retard":
        $fromdate=new datetime("4 years ago");
        $todate=new datetime("yesterday 23:59:59");
        break;
        
        case "rappels_futur":
        $fromdate=new datetime("tomorrow 0:0:0");
        $todate=new datetime("+".$_GET['until']." days");
        break;
        
        case "rappels_jour":
        $fromdate=new datetime();
        $fromdate->settime(0,0,0);
        $todate=clone $fromdate;
        $todate->modify("+".$_GET['until']." days");
        $todate->settime(23,59,59);
        break;
        
        case "sans_rappel":
        $function="norappel";
        break;
        
        default:
        $fromdate=$todate=null;
        break;
        //$fromdate=$todate=null;
    }

    if(!isset($_GET['order']) || empty($_GET['order'])) $_GET['order']="rappel";
    if(!isset($_GET['sensorder']) || empty($_GET['order'])) $_GET['sensorder']=0;

    $today=date("d/m/Y");
    
    $tr=array();
    $budget_total=0;
    foreach($projets->$function($commercial,$projet_etatid,$projet_typeid,$fromdate,$todate,$rappel_com,$nobudget,$noecheance,$nocmddelai,$srctxt,$dateecheance) as $projet){
        $prjusers="";
        foreach($projet->users() as $user){
            $prjusers.="<li style=\"".($user->userid==$projet->userid?"font-weight:bold;":"")."\"><a href=\"utilisateur.php?action=editer&userid=".$user->userid."\" target=\"_blank\">".trim($user->raisonsociale." ".$user->nom." ".$user->prenom)." (".$user->userid.")</a></li>";
        }
        
        $budget_total+=$projet->budget;
        $budget=number_format($projet->budget,2,",","&nbsp;");
        $creation=$projet->date->format("d/m/Y");
        if(!is_null($projet->dateecheance))
            $echeance=$projet->dateecheance->format("d/m/Y");
        else
            $echeance="";
            
        $orderkey=null;
        $rappels="";
        
        $listerappels=$projet->rappels();

        if($_GET['rappel']=="rappels_retard" || $_GET['rappel']=='' ) $listerappels=array_slice($listerappels,-1);

        foreach($listerappels as $rappel){
            if($_GET['order']=="rappel" && is_null($orderkey)) $orderkey=$rappel['ts'];
            $info=$rappel['origin']=="delai_cmd"?"Délai commande":$rappel['commercial'];
            $color=time()>=strtotime($rappel['date']->format("d-m-Y"))?"red":"";
            $rappels.="<span style=\"color:".$color."\">".$rappel['date']->format("d/m/Y".($rappel['date']->format("G")>0?" H:i":""))." ".$info."</span><br>";
        }

        switch($_GET['order']){
            case "date":
            $orderkey=$projet->date->gettimestamp();
            break;
            
            case "echeance":
            $orderkey=!is_null($projet->dateecheance)?$projet->dateecheance->gettimestamp():'';
            break;
            
            case "budget":
            $orderkey=$projet->budget;
            break;
        }
        
        $orderkey.=rand(10000,100000);

        $tr[$orderkey]=<<<HTML
            <tr id="flist">
                <td><ul>$prjusers</ul></td>
                <td><a href="projet.php?projetid=$projet->projetid" target="_blank">$projet->libelle $projet->pjt</a></td>
                <td>$projet->etat</td>
                <td>$projet->commercial</td>
                <td>{$projet->budget_type_lib()}</td>
                <td style="text-align:right;">$budget&nbsp;€</td>
                <td>$creation</td>
                <td>$echeance</td>
                <td>$rappels</td>
            </tr>
HTML;
    }

    if($_GET['sensorder']==0)
        ksort($tr);
    else
        krsort($tr);
    $table=implode("",$tr);

    $ordericon=array($_GET['order']=>$_GET['sensorder']==0?"⏶":"⏷");
    $newsens=abs(intval($_GET['sensorder'])-1);
    
    $budget_total=$budget_total>0?" (".number_format($budget_total,0,"."," ")."€)":"";
    
    echo "<center><h3>".count($tr)." résultat".(count($tr)>1?"s":"")." ".$budget_total."</h3></center><br>
    <table class=\"table_alternate_color\" style=\"width:1200px;\">
        <tr>
            <td class=\"entete\">Raison sociale</td>
            <td class=\"entete\">Projet</td>
            <td class=\"entete\">Etat</td>
            <td class=\"entete\">Commercial</td>
            <td class=\"entete\">Type</td>
            <td class=\"entete\" onclick=\"setorder('budget','".$newsens."')\">Budget".(isset($ordericon['budget'])?$ordericon['budget']:"")."</td>
            <td class=\"entete\" onclick=\"setorder('date','".$newsens."')\">Création".(isset($ordericon['date'])?$ordericon['date']:"")."</td>
            <td class=\"entete\" onclick=\"setorder('echeance','".$newsens."')\">Echéance".(isset($ordericon['echeance'])?$ordericon['echeance']:"")."</td>
            <td class=\"entete\" onclick=\"setorder('rappel','".$newsens."')\">Rappels".(isset($ordericon['rappel'])?$ordericon['rappel']:"")."</td>
        </tr>
        ".$table."
    </table>";
}

include "footer.php";
?>
