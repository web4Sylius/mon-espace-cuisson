<?php

require "admin_global.php";
include "./header.php";
include "./menu.php";
include "./fonctions.php";  
include "../class/facture.class.php";

$total_factures=0;

echo "<h1>Stat commandes enregistrées</h1>";

// preparation du filtre de recupération des factures
$sql="valide IN (1,5,7,8)";          // Validee, expedie, expedie en attente paiement, 

$tsfrom=mktime(0,0,0,date("m"),1,date("Y"));
$tsto=mktime(0,0,0,date("m"),date("d"),date("Y"));
if($tsfrom==$tsto){
    $yesterday=time()-86400;
    $tsfrom=mktime(0,0,0,date("m",$yesterday),1,date("Y",$yesterday));
}

if(isset($from) && $from!=""){
    $sp=explode("/",$from);
    $tsfrom=mktime(0,0,0,$sp[1],$sp[0],$sp[2]);
}
if(isset($to) && $to!=""){
    $sp=explode("/",$to);
    $tsto=mktime(23,59,59,$sp[1],$sp[0],$sp[2]);
}
$sql.=" AND heurefacture BETWEEN $tsfrom AND $tsto";

if(isset($_POST['prov'])){
    if($_POST['prov']['site']=="on"){
      	$provenance[]="";
		$provenance[]="mobile";
      	$chksite=" checked";
    }
    if($_POST['prov']['mp']=="on"){
      	$provenance[]="mp";
      	$chkmp=" checked";
    }
    if($_POST['prov']['sav']=="on"){
      	$provenance[]="sav";
    //  	$provenance[]="avoir"; 
      	$chksav=" checked";
    }
	
    $sql.=" AND provenance IN ('".implode("','",$provenance)."')";
}else
    $chksite=$chkmp=$chksav=" checked";

//echo $sql;

$f=new facture($DB_site2);
$list=$f->search($sql);

foreach($list as $facture){
    //echo "total de ".$facture->facturid." : ".$facture->totalht." (".$facture->provenance.")<br>";
    $total_factures+=$facture->totalht;

    $d=getdate($facture->datecommande);
    $tsdate=mktime(0,0,0,$d['mon'],$d['mday'],$d['year']);
    $ca[$tsdate]+=$facture->totalht;
    $nb[$tsdate]+=1;
}

/*$rq_facture=$DB_site2->query($sql);
while ($rs_facture=$DB_site2->fetch_array($rq_facture)){
            $remboursement = 0 ;
            $rowalt = "td_factures_" . getrowbg();
            list ($annee, $mois, $jour) = explode("-", $rs_facture[datefacture]);
	    if($rs_facture['heurefacture']!=0){
		$d=getdate($rs_facture['heurefacture']);
		$tsdate=mktime(0,0,0,$d['mon'],$d['mday'],$d['year']);
	    }
            $lignefactures=$DB_site2->query("SELECT * FROM lignefacture WHERE factureid = '$rs_facture[facturid]'");
            $total_facture = 0 ;
            while ($lignefacture=$DB_site2->fetch_array($lignefactures))
                {
                $total_facture += ($lignefacture[prix] * $lignefacture[qte])/(1+$lignefacture[tva]/100) ;
                if ($lignefacture[remboursement])
                    {
                    $remboursement += ($lignefacture[prix] * $lignefacture[qte])/(1+$lignefacture[tva]/100) ;
                    }
                }
        
            // Bon de réduction 
            $prixcadeau = 0;
            if (in_array("13", $modules) && $rs_facture[cadeauid] > 0) 
                {
                $cadeau = $DB_site2->query_first("select cadeauid, montantcadeau from facture where facturid = '$rs_facture[facturid]'");
                if ($cadeau[cadeauid] != "") 
                    {
                    $prixcadeau = $cadeau[montantcadeau];
                    }
                }
            // remise
            if (in_array("3", $modules)) 
                {
                $prixremise = 0;
                $remise_facture = $DB_site2->query_first("SELECT r.remiseid, r.typeremise, r.valeur, r.pctremise, f.montantremise FROM facture f inner join 
                remise r on (f.remiseid = r.remiseid) WHERE f.facturid = '$rs_facture[facturid]'");
                if ($remise_facture[remiseid] != "") 
                    {
                    $prixremise = $remise_facture[montantremise];
                    } // fin du if remise
                } // fin des remises
            // contre remboursement
            if ($rs_facture[moyenid] == '3') 
                {
                $prix_contre_remb = $rs_facture[prix_contre_remboursement];
                } 
            else 
                {
                $prix_contre_remb = 0;
                }

        $float_total_facture=$total_facture + $rs_facture[prixgestion] + $prix_contre_remb + ($rs_facture[prixport]/(1+$rs_facture[tvaport]/100)) - ($prixcadeau/1.196) - $prixremise;
	$total_factures+=$float_total_facture;

	$ca[$tsdate]+=$float_total_facture;
	$nb[$tsdate]+=1;
}*/

$ncust=array();
$lcust=array();
$sql="SELECT userid, UNIX_TIMESTAMP(dateinscription) as tsdate FROM utilisateur WHERE UNIX_TIMESTAMP(dateinscription) BETWEEN $tsfrom AND $tsto";
$req=$DB_site2->query($sql);
while($client=$DB_site2->fetch_array($req)){
    $day = mktime(0,0,0,date('n', $client['tsdate']),date('d', $client['tsdate']),date('Y', $client['tsdate']));
    $ncust[$day]++;
    $lost=$DB_site2->query_first("SELECT etat, date FROM suivi_client WHERE userid=".$client['userid']." ORDER BY date DESC LIMIT 1");
    $d=mktime(0,0,0,date("m",$lost['date']),date("d",$lost['date']),date("Y",$lost['date']));
    if($lost!="" && $lost['etat']==0) $lcust[$d]++;
}

echo <<<JAVASCRIPT
<style>
.ca { color:black; border-collapse:collapse; border:solid thin black; width:50%; margin-left:5%; margin-right:45%;}
.ca td { border:solid thin black; padding:1px; }
.ca_nb { text-align:right; white-space:nowrap; }
.ca_title { background-color:yellow; text-align:center; }
.ca_head { font-weight:bold; text-align:center; background-color:#dddddd; height:20p; border-bottom:solid thin black; }
.export { display:block; vertical-align:middle; text-decoration:none!important; }
.export img { width:20px; vertical-align:middle; }
</style>
JAVASCRIPT;


print "<LINK href=\"cal.css\" type=\"text/css\" rel=\"stylesheet\">
<script language=\"javascript\" src=\"calendrier.js\"></script>
<script language=\"javascript\">
initcalendrier();
</script>";

print "<div class=\"commande_detail\">";
print "<form method=\"post\">
<p class=\"column\">Provenance : <input type=\"checkbox\" name=\"prov[site]\"".$chksite."> Site <input type=\"checkbox\" name=\"prov[mp]\"".$chkmp."> Market Place <input type=\"checkbox\" name=\"prov[sav]\"".$chksav."> SAV</p>
<p class=\"column\">Du <input type=\"text\" name=\"from\" id=\"from\" size=\"10\" value=\"".(isset($from)?$from:"")."\" onclick=\"calendrier_show(this.id,this.value)\" readonly> au <input type=\"text\" name=\"to\" id=\"to\" size=\"10\" value=\"".(isset($to)?$to:"")."\" onclick=\"calendrier_show(this.id,this.value)\" readonly> <input type=\"submit\" value=\"OK\"></p>
</form>";
print "</div>";

echo "<br><br><br><br><br><br><br>&nbsp;&nbsp;(Validées, expédiées, payées et en attente de payement, transport et reduction incluses)<br><br>";

print "<table class=\"ca\">
<tr><td align=\"center\"><b>Date</b></td><td align=\"center\"><b>Nombre commandes</b></td><td align=\"center\"><b>CA HT</b></td><td><b>Nvx clients</b></td><td><b>Perdus</b></td></tr>";


$wnb=$wca=$wenb=$weca=$wecust=$wcust=$welcust=$wlcust=0;
$data="";
for($date=$tsfrom;$date<$tsto;$date+=90000){
    $color="";
    $d=getdate($date);
    $date=mktime(0,0,0,$d['mon'],$d['mday'],$d['year']); //pour changement d'heure
    $wd=date("w",$date);
    if($wd==0 || $wd==6){
      $color="style=\"color:red;\"";
      $wenb+=$nb[$date];
      $weca+=$ca[$date];
      $wecust+=$ncust[$date];
      $welcust+=$lcust[$date];
    }else{
      $wnb+=$nb[$date];
      $wca+=$ca[$date];
      $wcust+=$ncust[$date];
      $wlcust+=$lcust[$date];
    }
    $data.=",".$date.":".intval($ca[$date]);
    print ""
    . "<tr>"
        . "<td $color>".date("d/m/Y",$date)."</td>"
        . "<td align=\"center\">".intval($nb[$date])."</td>"
        . "<td align=\"right\">".number_format($ca[$date],2,',','')."</td>"
        . "<td align=\"center\">".intval($ncust[$date])."</td>"
        . "<td align=\"center\">".intval($lcust[$date])."</td>"
    . "</tr>";
    
    if($wd == 0){
        print ""
        . "<tr style=\"background-color:#eeeeee;\">"
            . "<td>Semaine ".date("W",$date)."</td>"
            . "<td align=\"center\">$wnb</td>"
            . "<td align=\"right\">".number_format($wca,2,',','')."</td>"
            . "<td align=\"center\">$wcust</td>"
            . "<td align=\"center\">$wlcust</td>"
        . "</tr>"
        . "<tr style=\"background-color:#eeeeee;\">"
            . "<td>Weekend ".date("W",$date)."</td>"
            . "<td align=\"center\">$wenb</td>"
            . "<td align=\"right\">".number_format($weca,2,',','')."</td>"
            . "<td align=\"center\">$wecust</td>"
            . "<td align=\"center\">$welcust</td>"
        . "</tr>"
        . "<tr style=\"background-color:#dddddd;\">"
            . "<td>Total ".date("W",$date)."</td>"
            . "<td align=\"center\">".($wnb+$wenb)."</td>"
            . "<td align=\"right\">".number_format($wca+$weca,2,',','')."</td>"
            . "<td align=\"center\">".($wcust+$wecust)."</td>"
            . "<td align=\"center\">".($wlcust+$welcust)."</td>"
        . "</tr>";

        $wnb=$wca=$wenb=$weca=$wcust=$wecust=$welcust=$wlcust=0;
    }
}
$data=substr($data,1);

$provfrance="";

if(isset($provenance)) foreach($provenance as $p) $provfrance.="&prov[]=".$p;

print "<tr><td><b>Total</b></td><td align=\"center\">".(isset($nb)?array_sum($nb):0)."</td><td align=\"right\">".(isset($ca)?number_format(array_sum($ca),2,',',''):"0,00")."</td><td align=\"center\">".(isset($ncust)?array_sum($ncust):0)."</td><td align=\"center\">".(isset($lcust)?array_sum($lcust):0)."</td></tr>";
print "</table>";
print "<p><img src=\"ccgraph.php?data=$data&width=600height=400&abstype=ts&title=$site&limit=$art[prix]\"></p>";
//pour debug, permet de voir les erreurs / problemes 
//print "<a href=\"";
//print "ccgraph.php?data=".$data."&width=600height=400&abstype=ts&title=".$site."&limit=".$art[prix];
//print "\" target=\"blank\"> clique ICI !!! </a>"; 

print "<div>
<p>Afficher <input type=\"radio\" name=\"counttype\" onclick=\"document.getElementById('francesvg').src='carte_france.php?from=$tsfrom&to=".$tsto.$provfrance."'\" checked> Total HT &nbsp; <input type=\"radio\" name=\"counttype\" onclick=\"document.getElementById('francesvg').src='carte_france.php?from=$tsfrom&to=$tsto&counttype=cmd'\"> Nombre de commandes &nbsp; <input type=\"radio\" name=\"counttype\" onclick=\"document.getElementById('francesvg').src='carte_france.php?from=$tsfrom&to=$tsto&counttype=qte'\"> Quantité d'articles</p>
<embed id=\"francesvg\" src=\"carte_france.php?from=".$tsfrom."&to=".$tsto.$provfrance."\" width=\"587\" height=\"550\">
</div>";

include "./footer.php";

?>
