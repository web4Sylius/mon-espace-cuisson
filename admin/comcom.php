<?php

require "admin_global.php";
include "./header.php";
include "./menu.php";

include("../class/facture.class.php");

if(!isset($pclist)) $pclist=array(0.3,0.2,0.3,0.4);
if(!isset($pcreduc)) $pcreduc=50;

echo "
<td valign=\"top\">
<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\"><tr><td>
<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
  <tr>
   <td>
    <table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
        <tr>
            <td height=\"25\" nowrap> &nbsp; <img src=\"images/arrow_2.jpg\"> <font class=\"cost\">Commissions</font>
            </td>
        </tr>
        <tr>
            <td background=\"images2/cadre_r3_c2.gif\" width=\"100%\" height=\"2\">
            </td>
        </tr>
    </table>
   </td>
   <td><img name=\"cadre_r2_c5\" src=\"images2/cadre_r2_c5.gif\" width=\"11\" height=\"27\" border=\"0\" alt=\"\"></td>
   <td background=\"images2/cadre_r2_c6.gif\" width=\"100%\" height=\"27\" align=\"center\" valign=\"middle\">
   </td>
   <td><img name=\"cadre_r2_c8\" src=\"images2/cadre_r2_c8.gif\" width=\"13\" height=\"27\" border=\"0\" alt=\"\"></td>
  </tr>
</table>

</tr></td>
<tr><td>

<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
  <tr>
   <td background=\"images2/cadre_r4_c2.gif\" width=\"3\" height=\"100%\"></td>
   <td align=\"justify\" valign=\"middle\">
    <font class=\"txt_2\">
        <table align=\"center\" width=\"100%\">
            <tr>
                <td align=\"center\" valign=\"middle\" width=\"100%\">" ;

print "<form action=\"comcom.php\" method=\"get\">
<table>
<tr><td>- de 300</td><td><input type=\"text\" name=\"pclist[]\" value=\"".$pclist[0]."\" size=\"2\">%</td></tr>
<tr><td>300 - 1000</td><td><input type=\"text\" name=\"pclist[]\" value=\"".$pclist[1]."\" size=\"2\">%</td></tr>
<tr><td>1000 - 1500</td><td><input type=\"text\" name=\"pclist[]\" value=\"".$pclist[2]."\" size=\"2\">%</td></tr>
<tr><td>+ de 1500</td><td><input type=\"text\" name=\"pclist[]\" value=\"".$pclist[3]."\" size=\"2\">%</td></tr>
<tr><td>Avec réduction</td><td>-<input type=\"text\" name=\"pcreduc\" value=\"".$pcreduc."\" size=\"2\">%</td></tr>
</table>
<input type=\"submit\" value=\"OK\">
</form><br>";

$listmois=array();
$totalc=array();
$f=new facture($DB_site2);
$factures=$f->search("commercial<>'' AND valide IN (1,5,7,8) AND heurefacture BETWEEN 1325371305 AND UNIX_TIMESTAMP(NOW())");
foreach($factures as $facture){
    $coeff=1;
    $mois=mktime(0,0,0,date("m",$facture->datecommande),1,date("Y",$facture->datecommande));
    $listmois[]=$mois;
    $totalcommande=commission($facture->totalht);
    if($facture->cadeauht>0 || $facture->cadeauid!="0") $coeff=(1-$pcreduc/100);
    $total[$facture->commercial][$mois]+=($totalcommande*$coeff);
    $totalc[$facture->commercial][$mois]+=$facture->totalttc;
// print $facture->facturid." ".($totalcommande*$coeff)."<br>";
}

$listmois=array_unique($listmois);

print "<table border=\"1\">
<tr><td>Commercial</td>";
foreach($listmois as $m) print "<td>".date("m/Y",$m)."</td>";
print "</tr>";
foreach($total as $commercial=>$mois){
    print "<tr><td>".$commercial."</td>";
    foreach($listmois as $m){
	print "<td>".round($mois[$m])."&euro;<br>".round($totalc[$commercial][$m])."&euro;</td>";
    }
    print "</tr>";
}
print "</tables>";

// print jour_ouvre(mktime(0,0,0,9,1,2012));

function commission($totalht){
    global $pclist;
    switch($totalht){
	case $totalht<=300:
	$pc=$pclist[0];
	break;
	
	case $totalht>300 && $totalht<=1000:
	$pc=$pclist[1];
	break;

	case $totalht>1000 && $totalht<1500:
	$pc=$pclist[2];
	break;
  
	case $totalht>=1500:
	$pc=$pclist[3];
	break;
    }
    return $totalht*($pc/100);
}

function jour_ouvre($ts=null){
    if($ts==null) $ts=time();
    $yearts=date("Y",$ts);
    $ouvre=array(1,2,3,4,5);

    $ferie=array(mktime(0,0,0,1,1,$yearts),mktime(0,0,0,5,1,$yearts),mktime(0,0,0,5,8,$yearts),mktime(0,0,0,7,14,$yearts),mktime(0,0,0,8,15,$yearts),mktime(0,0,0,11,1,$yearts),mktime(0,0,0,11,11,$yearts),mktime(0,0,0,12,25,$yearts));
    $paques=easter_date($yearts)+86400;
    $ferie[]=$paques;

    $ascension=easter_date($yearts)+(39*86400);
    $ferie[]=$ascension;

    $pentecote=easter_date($yearts)+(50*86400);
    $ferie[]=$pentecote;

    $d1=mktime(0,0,0,date("m",$ts),1,date("Y",$ts));
    $nbjours=date("t",$d1)-1;

    $ret=0;
    for($i=0;$i<=$nbjours;$i++){
	$jts=$d1+(86400*$i);
	$jsem=date("w",$jts);
	if(in_array($jsem,$ouvre) && !in_array($jts,$ferie)) $ret++;
    }
    return $ret;
}

echo "</td>
            </tr>   
        </table>    
     </font>
    </td>
   <td background=\"images2/cadre_r4_c10.gif\" width=\"3\" height=\"100%\" border=\"0\" alt=\"\"></td>
  </tr>
</table>
</td>
</tr>
<tr><td>

<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
  <tr>
   <td rowspan=\"2\"><img name=\"cadre_r6_c2\" src=\"images2/cadre_r6_c2.gif\" width=\"10\" height=\"26\" border=\"0\" alt=\"\"></td>
   <td align=\"center\" height=\"23\" width=\"100%\"></td>
   <td rowspan=\"2\"><img name=\"cadre_r6_c9\" src=\"images2/cadre_r6_c9.gif\" width=\"8\" height=\"26\" border=\"0\" alt=\"\"></td>
  </tr>
  <tr>
   <td background=\"images2/cadre_r7_c4.gif\" width=\"100%\" height=\"3\"></td>
  </tr>
</table>
</td></tr></table>
</td>
</tr><tr>
</tr></table>
                  </td>
              </tr>
            </table>
          </td>
        </tr>
";

include "./footer.php";


?>