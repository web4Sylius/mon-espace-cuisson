<?php

require "admin_global.php";
include "./header.php";
include "./menu.php";

include("stat/stat.php");

$stat=new ccstat();

if(isset($_GET['start'])) $stat->start=$_GET['start'];
if(isset($_POST['start'])) $stat->start=$_POST['start'];
if(!isset($_GET['mode'])) $_GET['mode']="d";
$stat->mode=$_GET['mode'];
$stat->init();

$stat->calcstat();

print "<script language=\"javascript\">\n

     function view(divid){\n
       document.getElementById('defaut').style.visibility='hidden';
       document.getElementById('fai').style.visibility='hidden';
       document.getElementById('geo').style.visibility='hidden';
       document.getElementById('mr').style.visibility='hidden';
       document.getElementById('os').style.visibility='hidden';
       document.getElementById('browser').style.visibility='hidden';
       document.getElementById('prov').style.visibility='hidden';
       document.getElementById('pages').style.visibility='hidden';
       document.getElementById(divid).style.visibility='visible';
       window.location='#bottom';
       if(divid==\"geo\") sendxmlmap();
     }

      function detailprov(id){
	  if(document.getElementById('plus_'+id).src.indexOf(\"images/plus.jpg\")>-1){
	      document.getElementById('plus_'+id).src=\"images/moins.jpg\";
	      document.getElementById('detailprov_'+id).style.position='relative';
	      document.getElementById('detailprov_'+id).style.visibility='visible';
	  }else{
	      document.getElementById('plus_'+id).src='images/plus.jpg';
	      document.getElementById('detailprov_'+id).style.position='absolute';
	      document.getElementById('detailprov_'+id).style.visibility='hidden';
	  }
      }
      
      function detailpage(id){
	  if(document.getElementById('pluspage_'+id).src.indexOf(\"images/plus.jpg\")>-1){
	      document.getElementById('pluspage_'+id).src=\"images/moins.jpg\";
	      document.getElementById('detailpage_'+id).style.position='relative';
	      document.getElementById('detailpage_'+id).style.visibility='visible';
	  }else{
	      document.getElementById('pluspage_'+id).src='images/plus.jpg';
	      document.getElementById('detailpage_'+id).style.position='absolute';
	      document.getElementById('detailpage_'+id).style.visibility='hidden';
	  }
      }

      function detailmr(id){
	  if(document.getElementById('plusmr_'+id).src.indexOf(\"images/plus.jpg\")>-1){
	      document.getElementById('plusmr_'+id).src=\"images/moins.jpg\";
	      document.getElementById('detailmr_'+id).style.position='relative';
	      document.getElementById('detailmr_'+id).style.visibility='visible';
	  }else{
	      document.getElementById('plusmr_'+id).src='images/plus.jpg';
	      document.getElementById('detailmr_'+id).style.position='absolute';
	      document.getElementById('detailmr_'+id).style.visibility='hidden';
	  }
      }
     </script>";

echo "
<style>
.ca { color:black; border-collapse:collapse; border:solid thin black; }
.ca td { border:solid thin black; }
.ca_nb { text-align:right; white-space:nowrap; }
.ca_title { background-color:yellow; text-align:center; }
.ca_head { font-weight:bold; text-align:center; }
</style>
<td valign=\"top\">
<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\"><tr><td>
<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
  <tr>
   <td>
    <table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
        <tr>
            <td height=\"25\" nowrap> &nbsp; <img src=\"images/arrow_2.jpg\"> <font class=\"cost\">Chiffre d'affaire</font>
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
   <td align=\"justify\" valign=\"middle\"><br>";

print "<table width=\"800\" cellspacing=\"0\" cellpadding=\"0\" bgcolor=\"white\" align=\"center\">
<tr height=\"30\" valign=\"top\"><td class=\"cbnred14\">Statistiques</td><td><a href=\"?page=stat&start=".date("d/m/Y")."&mode=d\" class=\"gray12d\">Aujourd'hui</a> &nbsp; <a href=\"?page=stat&start=".date("1/m/Y")."&mode=m\" class=\"gray12d\">Mois en cours</a> &nbsp; <a href=\"?page=stat&start=".date("1/1/Y")."&mode=y\" class=\"gray12d\">Année en cours</a>
    <br><form method=\"get\"><input type=\"hidden\" name=\"page\" value=\"stat\"><div id=\"calendrier\"></div><input type=\"text\" name=\"start\" id=\"start\" value=\"".$start."\"> <a href=\"javascript:calendrier_show('start')\" class=\"gray12\">Calendrier</a> <input type=\"submit\" value=\"OK\"></form>
    <br><a href=\"?page=stat&start=".$start."&export=pdf\"></a></td></tr>
<tr><td colspan=\"2\" class=\"black10\">".$periode."</td></tr>
<tr><td colspan=\"2\">";
print "<img src=\"stat/graph.php?data=".$stat->graphdata()."&mode=".$_GET['mode']."\">";
print "</td></tr>
<br><a href=\"javascript:view('geo')\" class=\"gray12d\">Provenance géographique</a>
<br><a href=\"javascript:view('fai')\" class=\"gray12d\">FAI</a>
<br><a href=\"javascript:view('os')\" class=\"gray12d\">OS</a>
<br><a href=\"javascript:view('browser')\" class=\"gray12d\">Navigateur</a>
<br><a href=\"javascript:view('prov')\" class=\"gray12d\">Provenance</a>
<br><a href=\"javascript:view('mr')\" class=\"gray12d\">Indexation</a>
<br><a href=\"javascript:view('pages')\" class=\"gray12d\">Pages vues</a>";
if(isset($_GET['ifai'])) print "<br><a href=\"javascript:view('ifai')\" class=\"gray12d\">FAI inconnus</a>";
print "</td><td>";

$divstyle="visibility:hidden;border-style:solid; border-width:1; position: absolute; left:position-gauche; top: position-haut; z-index:1; background-color:#FFFFFF;";

// defaut

$nbmembres=count($stat->membre);
print "<div id=\"defaut\" style=\"border-style:none; border-width:0; position: absolute; left:position-gauche; top: position-haut; z-index:1; background-color:#FFFFFF;\">
<table bgcolor=\"#FFFFFF\">
   <tr class=\"gray10\"><td>Visiteurs r&eacute;els uniques par jour :</td><td>".$stat->chostcount."</td></tr>\n
   <tr class=\"gray10\"><td>Nouveaux visiteurs :</td><td>$stat->newvisitor</td><td>".round(100*$stat->newvisitor/$stat->chostcount,2)." %</td></tr>\n
   <tr class=\"gray10\"><td>Taux de retour :</td><td>".round(($stat->chostcount-$stat->newvisitor)/$stat->chostcount*100,2)." %</td></tr>\n
   <tr><td>Visiteurs identifiés :</td><td>".$nbmembres."</td><td>".round(100*$nbmembres/$stat->chostcount,2)." %</td></tr>\n
   <tr class=\"gray10\"><td>Moyenne :</td><td>".round($stat->chostcount/$stat->len*3600,2)." visiteurs/h</td><td>&nbsp;</td></tr>";

print "<tr class=\"gray10\"><td>Pages vues :</td><td>".$stat->pagecount."</td></tr>
<tr><td></td></tr>";

print "<tr class=\"gray10\"><td>Commandes :</td><td>$stat->orderscount</td></tr>
<tr><td>Chiffre d'affaires :</td><td>$stat->orderssum €</td></tr>
</table>\n$foot</div>\n";

    // provenance geo
print "<div id=\"geo\" style=\"".$divstyle."\">\n<table>\n
<tr><td colspan=\"2\" bgcolor=\"#C0C0C0\">Provenance géographique</td></tr>
<tr><td colspan=\"2\">
<object type=\"application/x-shockwave-flash\" data=\"stat/france_dept.swf\" width=\"500\" height=\"500\" id=\"pmap\" align=\"middle\">\n
<param name=\"allowScriptAccess\" value=\"sameDomain\" />\n
<param name=\"movie\" value=\"stat/france_dept.swf\" />\n
<param name=\"quality\" value=\"high\" />\n
</object>
</td></tr>
<tr><td valign=\"top\">";
ksort($stat->loc);
/*print "<tr><td class=\"black10\">Pays</td></tr>\n";
foreach($country as $cle=>$valeur){
     print "<tr class=\"grey10\"><td><img src=\"images/flags/".$cle.".png\"> ".country_name($cle)."</td><td colspan=\"2\">$valeur<br><img src=\"../images/hbar.png\" width=\"".round($valeur/$membrecount*100)."\" height=\"10\"></td></tr>\n";
}
print "<tr><td>&nbsp;</td></tr>"; */
print "<table>\n<tr><td class=\"gray12d\">Par connexion</td></tr>\n";
arsort($stat->loc);
foreach($stat->loc as $cle=>$valeur){
    if($cle==""){
         $town="Inconnu";
    }else{
         $town="<a href=\"http://maps.google.fr/maps?f=q&hl=fr&q=".$cle."\" target=\"blank\">".$cle."</a>";
         echo "<tr class=\"gray10\"><td>".$town."</td><td><a href=\"admin/members.php?town=".urlencode($cle)."&sst=".$stat->sst."&est=".$stat->est."\" target=\"_blank\"></td><td>".$valeur."<br><img src=\"../images/hbar.png\" width=\"".round($valeur/$stat->membrecount*100)."\" height=\"10\"></td></tr>\n";
    }
}
print "<tr bgcolor=\"#C0C0C0\"><td>&nbsp;</td><td colspan=\"2\">&nbsp;</td></tr>\n</table>\n </td>
<td valign=\"top\"><table>\n<tr><td class=\"gray12d\">Par membre</td></tr>\n";
$pxml="<xml>";
arsort($userville);
foreach($userville as $cle=>$valeur){
    if($cle==""){
         $town="Inconnu";
    }else{
        $tinfo=gettowninfos($cle);
        $pxml.="<town long=\"".$tinfo->longitude."\" lat=\"".$tinfo->latitude."\" nb=\"".$valeur."\">".$cle."</town>";
         $town="<a href=\"http://maps.google.fr/maps?f=q&hl=fr&q=".$cle."\" target=\"blank\">".ucfirst($cle)."</a>";
         echo "<tr class=\"gray10\"><td>".$town."</td><td><a href=\"admin/members.php?town=".urlencode($cle)."&sst=".$start->sst."&est=".$start->est."\" target=\"_blank\"></td><td>".$valeur."<br><img src=\"../images/hbar.png\" width=\"".round($valeur/$stat->membrecount*100)."\" height=\"10\"></td></tr>\n";
    }
}
$pxml.="</xml>";
print "</table>\n</td></tr>\n</table>$foot</div>\n";

// fai
arsort($stat->chost);
print "<div id=\"fai\" style=\"".$divstyle."\">
<table bgcolor=\"#FFFFFF\">\n<tr><td>\n<table>\n<tr bgcolor=\"#C0C0C0\" colspan=\"2\"><td>Acc&egrave;s des visiteurs</td></tr>\n";
foreach($stat->chost as $cle=>$valeur){
    echo "<tr class=\"gray10\"><td><a href=\"".$stat->sitehost[$cle]."\" target=\"_blank\">".$cle."</a></td><td>".$valeur." - ".round($valeur/$stat->chostcount*100,2)." %<br><img src=\"../images/hbar.png\" width=\"".round($valeur/$stat->chostcount*400)."\" height=\"10\"></td></tr>\n";
}
print "<tr class=\"gray10\"><td>Adresse IP</td><td>".$stat->iphostcount." - ".round($stat->iphostcount/$stat->chostcount*100,2)." %<br><img src=\"../images/hbar.png\" width=\"".round($stat->iphostcount/$stat->chostcount*400)."\" height=\"10\"></td></tr>\n";
print "<tr><td bgcolor=\"#C0C0C0\">Total</td><td bgcolor=\"#C0C0C0\">".$stat->chostcount." (".round(($stat->chostcount/$stat->hostcount*100),2)." %)</td></tr>\n";
print "</table>\n</td></tr>\n";
arsort($stat->countries);
foreach($stat->countries as $cle=>$valeur){
     $countryname=$stat->countrylist[$cle];
     print "<tr><td><img src=\"../images/flags/$cle.png\"> $countryname</td><td>$valeur</td></tr>";
}
print "<tr><td>&nbsp;</td></tr>\n</table>\n$foot</div>\n";

   // os des visiteurs
ksort($stat->os);
print "<div id=\"os\" style=\"".$divstyle."\">
<table bgcolor=\"#FFFFFF\">
<tr bgcolor=\"#C0C0C0\"><td colspan=\"2\">Syst&egrave;mes d'exploitation</td></tr>\n";
foreach($stat->os as $cle=>$valeur){
    if($cle=="") $cle="Autre";
    echo "<tr class=\"gray10\"><td>".$cle."</td><td>".$valeur." - ".round($valeur/$stat->chostcount*100,2)." %<br><img src=\"../images/hbar.png\" width=\"".round($valeur/$stat->chostcount*200)."\" height=\"10\"></td></tr>\n";
}
print "</table>\n$foot</div>\n";

   // navigteurs
ksort($stat->browser);
print "<div id=\"browser\" style=\"".$divstyle."\">
<table bgcolor=\"#FFFFFF\">
<tr bgcolor=\"#C0C0C0\"><td colspan=\"2\">Navigateurs</td></tr>\n";
foreach($stat->browser as $cle=>$valeur){
    if($cle=="") $cle="Autre";
    echo "<tr class=\"gray10\"><td>".$cle."</td><td>".$valeur." - ".round($valeur/$stat->chostcount*100,2)." %<br><img src=\"../images/hbar.png\" width=\"".round($valeur/$stat->chostcount*200)."\" height=\"10\"></td></tr>\n";
}
print "</table>\n$foot</div>\n";

      // provenance
$rech=array();
$mrn=array();
$prov=array();
print "<div id=\"prov\" style=\"".$divstyle."\">
<table bgcolor=\"#FFFFFF\">
<tr><td bgcolor=\"#C0C0C0\" colspan=\"2\">Provenance</td><td colspan=\"2\" bgcolor=\"#C0C0C0\">&nbsp;</td></tr>
<tr><td>Saisie directe</td><td>".$stat->directlink."</td></tr>\n";


ksort($stat->sitelink);
$i=0;
foreach($stat->sitelink as $site=>$s){
	$nbtotal=0;
        print "<tr><td><img src=\"images/plus.jpg\" width=\"22\" id=\"plus_$i\" onclick=\"detailprov($i);\" align=\"absmiddle\"> ".$site."<div id=\"detailprov_$i\" style=\"visibility:hidden; position:absolute; padding-left:25px;\">";
	$br="";
        foreach($s as $ref=>$nb){
             echo $br."<a href=\"redirect.php?url=".urlencode($ref)."\" target=\"_blank\" class=\"gray10\">".$ref."</a> ".$nb;
	      $br="<br>";
	      $nbtotal+=$nb;
        }
	print "</div></td><td>$nbtotal</td></tr>\n";
	$i++;
}

print "<tr><td>&nbsp;</td></tr>\n<tr><td bgcolor=\"#C0C0C0\">Moteurs de recherche</td></tr>\n";
$i=0;
foreach($stat->mrlink as $mrname=>$l){
        print "<tr><td><img src=\"images/plus.jpg\" width=\"22\" id=\"plusmr_$i\" onclick=\"detailmr($i);\" align=\"absmiddle\"> ".$mrname."</td><td>".count($l)."</td></tr>
	<tr><td colspan=\"2\"><table id=\"detailmr_$i\" style=\"position:absolute; visibility:hidden; margin-left:20px;\">\n";
        foreach($l as $ref=>$nb){
                   $str=$ref;
                   if(isset($stat->mrsrc[$ref])) $str=$stat->mrsrc[$ref];
                    print "<tr class=\"gray10\"><td><a href=\"redirect.php?url=".urlencode($ref)."\" target=\"_blank\" class=\"gray10\">".$str."</a></td><td class=\"grey10\">$nb - ".round($pc,2)."</td></tr>\n";
        }
	print "</table>
	</td></tr>";
	$i++;
}

print "<tr><td>&nbsp;</td></tr>\n<tr><td bgcolor=\"#C0C0C0\">Mot recherch&eacute;s</td></tr>\n";
arsort($stat->rechstr);
foreach($stat->rechstr as $cle=>$valeur){
    if($cle!="") print "<tr class=\"gray10\"><td>$cle</td><td class=\"grey10\">$valeur</td></tr>\n";
}

print "</table>\n$foot</div>\n";

// moteurs de recherche
print "<div id=\"mr\" style=\"".$divstyle."\">
<table bgcolor=\"#FFFFFF\">
<tr bgcolor=\"#C0C0C0\"><td colspan=\"2\">Passage des moteurs de recherche</td><td></td></tr>\n";
ksort($stat->mrhost);
foreach($stat->mrhost as $cle=>$valeur){
    echo "<tr class=\"gray10\"><td>".$cle." <a href=\"stat/mrindex.php?mr=".$cle."&sst=".$stat->sst."&mode=".$_GET['mode']."\" target=\"_blank\">détail</a></td><td>".$valeur." - ".round($valeur/$stat->mrhostcount*100,2)." %<br><img src=\"../images/hbar.png\" width=\"".round($valeur/$stat->mrhostcount*200)."\" height=\"10\"></td></tr>\n";
}
print "<tr><td bgcolor=\"#C0C0C0\">Total</td><td bgcolor=\"#C0C0C0\">".$stat->mrhostcount." (".round(($stat->mrhostcount/$stat->hostcount*100),2)." %)</td></tr>\n";
print "<tr bgcolor=\"#C0C0C0\"><td>Pages indexées</td><td></td></tr>\n";
foreach($stat->indexpage as $name=>$pages){
       print "<tr><td>".$name."</td></tr>\n";
       foreach($pages as $title=>$nb){
               echo "<tr class=\"gray10\"><td>".$title."</td><td>".$nb."</td></tr>\n";
       }
}
print "</table>\n".$foot."</div>\n";

// pages vues
print "<div id=\"pages\" style=\"".$divstyle."\">
<table bgcolor=\"#FFFFFF\" width=\"300\">
<tr><td bgcolor=\"#CCCCCC\" colspan=\"2\">Pages vues</td></tr>\n";


$vpkeys=array_keys($stat->vpage);
$i=0;
foreach($vpkeys as $key){
       print "<tr height=\"20\"><td><img src=\"images/plus.jpg\" id=\"pluspage_$i\" width=\"22\" align=\"absmiddle\" onclick=\"detailpage($i);\"> ".$key."</td><td>".array_sum($stat->vpage[$key])."</td></tr>\n";
       arsort($stat->vpage[$key]);
	 echo "<tr><td colspan=\"2\"><table id=\"detailpage_".$i."\" style=\"position:absolute; visibility:hidden; margin-left:20px;\">";
       foreach($stat->vpage[$key] as $page=>$nb){
             echo "<tr class=\"gray10\"><td>".$page."</td><td>".$nb."</td></tr>\n";
       }
	echo "</table></td></tr>";
	$i++;
}


print "</table></div>";

// fai inconnus
if(isset($_GET['ifai'])){
      print "<div id=\"ifai\" style=\"".$divstyle."\" class=\"black10\">".$stat->unkfai."</div>";
}

print "</td></tr>
</table>";


echo "
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