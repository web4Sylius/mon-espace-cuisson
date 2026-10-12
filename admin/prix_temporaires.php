<?php

require "admin_global.php";

include "./header.php";
include "./menu.php";

$now=time();

$optmarque="<option>Toutes</option>";
$req=$DB_site2->query("SELECT marqueid,libelle FROM marque ORDER BY libelle");
while($m=$DB_site2->fetch_array($req)){
    $marques[$m['marqueid']]=$m['libelle'];
    $optmarque.="<option value=\"".$m['marqueid']."\"".($_GET['marqueid']==$m['marqueid']?" selected":"").">".$m['libelle']."</option>";
}

$site_code="base";
if(isset($_GET['site_code'])) $site_code=$_GET['site_code'];

$devise=$devises->symbole($sites[$site_code]['devise']);
$taux=$DB_site2->query_first("SELECT 0,TVAtauxnormal as taux1,TVAtauxreduit as taux2 FROM pays WHERE paysid=".$sites[$site_code]['paysid_ref']);


$sql="SELECT a_s.artid,artcode,artcode_eurocave,a_s.tauxchoisi,a_s.prix,a_s.active,tp.du,tp.au,tp.prix AS tprix,tp.prixconseilleht 
FROM article INNER JOIN temp_price tp USING(artid) INNER JOIN article_marque am USING(artid) INNER JOIN article_site a_s USING(artid)
WHERE a_s.site_code='".$site_code."'";

switch($_GET['period']){
    case -1:
    $sql.=" AND au<".$now;
    $chkp[-1]="checked";
    break;

    case 1:
    $sql.=" AND du>".$now;
    $chkp[1]="checked";
    break;

    case 0:
    default:
    $sql.=" AND du<".$now." AND au>".$now;
    $chkp[0]="checked";
}
if(isset($_GET['marqueid']) && intval($_GET['marqueid'])>0)
  $sql.=" AND am.marqueid=".intval($_GET['marqueid']);

switch($_GET['order']){
    case "ref":
    $sql.=" ORDER BY artcode_eurocave";
    break;
    
    case "prix":
    $sql.=" ORDER BY a_s.prix";
    break;
    
    case "prixconseille":
    $sql.=" ORDER BY tp.prixconseilleht";
    break;
    
    case "tprix":
    $sql.=" ORDER BY tp.prix";
    break;
    
    case "pc":
    $sql.=" ORDER BY (1-(tp.prix/tp.prixconseilleht))";
    break;
    
    case "du":
    $sql.=" ORDER BY du";
    break;
    
    case "au":
    $sql.=" ORDER BY au";
    break;
    
    case "artcode":
    default:
    $sql.=" ORDER BY artcode";
}
  
$ordm[$_GET['order']]=" &#9660;";

$req=$DB_site2->query($sql);
$nbtp=$DB_site2->num_rows($req);
if($nbtp>0){
    $tbl=<<<HTML
    <p>&nbsp;</p>
    <table class="tableUpdArt table_alternate_color">
    <tr>
      <td class="entete" colspan="8">$nbtp Prix temporaires</td>
    </tr>
    <tr class="entete_categorie">
      <td class="entete2"><a href="#" onclick="document.getElementsByName('order')[0].value='artcode';document.forms.src.submit();">Article</a>$ordm[artcode]</td>
      <td class="entete2"><a href="#" onclick="document.getElementsByName('order')[0].value='ref';document.forms.src.submit();">Référence</a>$ordm[ref]</td>
      <td class="entete2"><a href="#" onclick="document.getElementsByName('order')[0].value='prix';document.forms.src.submit();">Prix d'origine</a>$ordm[prix]</td>
      <td class="entete2"><a href="#" onclick="document.getElementsByName('order')[0].value='prixconseille';document.forms.src.submit();">Prix barré</a>$ordm[prixconseille]</td>
      <td class="entete2"><a href="#" onclick="document.getElementsByName('order')[0].value='tprix';document.forms.src.submit();">Prix temporaire</a>$ordm[tprix]</td>
      <td class="entete2"><a href="#" onclick="document.getElementsByName('order')[0].value='pc';document.forms.src.submit();">% réduction</a>$ordm[pc]</td>
      <td class="entete2"><a href="#" onclick="document.getElementsByName('order')[0].value='du';document.forms.src.submit();">Du</a>$ordm[du]</td>
      <td class="entete2"><a href="#" onclick="document.getElementsByName('order')[0].value='au';document.forms.src.submit();">Au</a>$ordm[au]</td>
    </tr>
HTML;
    
    while($article=$DB_site2->fetch_array($req)){
	$color=($article['active']==1?"green":"red");
	$du=date("d/m/Y",$article['du']);
	$au=date("d/m/Y",$article['au']);
	$prixorigin=number_format($article['prix'],2).$devise;
	$prix=number_format($article['tprix'],2).$devise;
	$prixconseille=$article['prixconseilleht']*(1+$taux['taux'.$article['tauxchoisi']]/100);
	$pc=round(-100*(1-$article['tprix']/$prixconseille))."%";
	$prixconseille=number_format($prixconseille,2).$devise;
    
	$tbl.=<<<HTML
	<tr>
	  <td><a href="ajout_produit.php?artid=$article[artid]&action2=prix" target="_blank" style="color:$color;">$article[artcode]</a></td>
	  <td><i>$article[artcode_eurocave]</i></td>
	  <td>$prixorigin</td>
	  <td><s>$prixconseille</s></td>
	  <td>$prix</td>
	  <td>$pc</td>
	  <td>$du</td>
	  <td>$au</td>
	</tr>
HTML;
    }
    
    $tbl.="</table>";
}else
    $tbl="<p>Aucun prix temporaire</p>";
  
print <<<HTML
<h1>Prix de vente temporaires</h1>

<div>
  <form method="get" name="src">
  <input type="hidden" name="order" value="$_GET[order]">
  Trier par marque : <select name="marqueid" onchange="document.forms.src.submit()">$optmarque</select> 
  <div class="separateur_vertical"></div>
  <input type="radio" name="period" value="-1" onchange="document.forms.src.submit()" {$chkp[-1]}>Offres expirées
  <input type="radio" name="period" value="0" onchange="document.forms.src.submit()" $chkp[0]>Offres actuelles
  <input type="radio" name="period" value="1" onchange="document.forms.src.submit()" $chkp[1]>Offres futures
  <div class="separateur_vertical"></div>

  $tbl
HTML;




?>