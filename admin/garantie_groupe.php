<?php

// GARANTIE

include("admin_global.php");

include "./header.php";
include "./menu.php";
include "../class/garantie.class.php";
include "../class/DAOgarantie.class.php";
include "../class/DAOtype.class.php";


if((isset($_POST['garchk']) || isset($_POST['supp_before'])) && isset($_POST['chk'])){
    $garobjlist=array();
    if(isset($_POST['garchk'])){
	foreach($_POST['garchk'] as $garantieid=>$on){
	    $garobjlist[$garantieid]=new garantie($DB_site2,"FR",$garantieid);
	}
    }

    $resultnb=0;
    foreach($_POST['chk'] as $artid=>$value){
	if(isset($_POST['supp_before'])) $DB_site2->query("DELETE FROM article_garantie_liste WHERE artid=".$artid);
	$contrats=$DB_site2->query_first("SELECT COUNT(artid) FROM article_garantie WHERE artid=".$artid);
	$nbcontrats=$contrats[0]==0?1:$contrats[0];
	foreach($garobjlist as $garantieid=>$garantie){
	    $paht=floatval($garantie->paht*$nbcontrats);
	    $prix=floatval($garantie->prix*$nbcontrats);
	    $ppi=floatval($garantie->ppi*$nbcontrats);
	    $result=$DB_site2->query("INSERT INTO article_garantie_liste SET artid=".$artid.", garantieid=".$garantieid.", paht=".$paht.", prix=".$prix.", ppi=".$ppi);
	    $resultnb+=($result?1:0);
	}
    }
    
    $updateresult="<p>".$resultnb." garanties ajoutées sur ".(count($_POST['garchk'])*count($_POST['chk']))."</p>";
}


$active=array(0=>"<span style=\"color:red\">Inactif</span>",1=>"Visible",2=>"<span style=\"color:red\">Supprimé</span>");

$typefield="<select name=\"typearticleid\">";
$req=$DB_site2->query("SELECT * FROM typearticle ORDER BY libelle");
while($typearticle=$DB_site2->fetch_array($req)){
    $nbinfo="";
    if(isset($_POST['marqueid'])){
	$nb=$DB_site2->query_first("SELECT COUNT(*) FROM article a INNER JOIN article_marque USING(artid) WHERE typearticleid=".$typearticle['typearticleid']." AND marqueid=".intval($_POST['marqueid'])." AND (SELECT COUNT(*) FROM article_lot WHERE lotid=a.artid)=0");
	$nbinfo=" (".$nb[0].")";
    }
    $typefield.="<option value=\"".$typearticle['typearticleid']."\"".($_POST['typearticleid']==$typearticle['typearticleid']?"selected":"").">".$typearticle['libelle'].$nbinfo."</option>";
}
$typefield.="</select>";

$marquefield="<select name=\"marqueid\">";
$req=$DB_site2->query("SELECT * FROM marque ORDER BY libelle");
while($m=$DB_site2->fetch_array($req))
    $marquefield.="<option value=\"".$m['marqueid']."\"".($_POST['marqueid']==$m['marqueid']?"selected":"").">".$m['libelle']."</option>";
$marquefield.="</select>";

$pays=array("RE"=>"Reste de l'Europe","RM"=>"Reste du monde");
$req=$DB_site2->query("SELECT * FROM pays");
while($p=$DB_site2->fetch_array($req)){
    $pays[$p['paysid']]=$p['diminutif'];
}

$garobjlist=array();
if(isset($_POST['marqueid']) && isset($_POST['typearticleid'])){
    $marqueid=intval($_POST['marqueid']);
    $typearticleid=intval($_POST['typearticleid']);
    $garantiesAvantTri = DAOgarantie::loadGarantiesByTypeMarque($DB_site2,'base','FR',$typearticleid,$marqueid);
    
    if(count($garantiesAvantTri)>0){
	$garlist="<h2>Contrats disponibles</h2>
	<table class=\"tbl\">
	<thead>
	<tr>
	  <td>Garantie</td>
	  <td>Fournisseur</td>
	  <td>Type</td>
	  <td>Pays</td>
	  <td>PA HT</td>
	  <td>PV TTC</td>
	  <td>PPI TTC</td>
	</tr>
	</thead>";
	
	foreach($garantiesAvantTri as $g){
	    $paysliste=array();
	    foreach($g->pays as $gpays)
		$paysliste[$gpays]=$pays[$gpays];
	    asort($paysliste);
	
	    $garlist.="<tr>
	      <td class=\"garantieid\">".$g->garantieid."</td>
	      <td>".$g->fournisseur."</td>
	      <td>".($g->cumul==1?"+":"").$g->duree." an".($g->duree>1?"s ":" ").$g->getTypeGarantie().($g->utilisation_garantie==0?" fournisseur":" complémentaire")."</td>
	      <td>".implode(", ",$paysliste)."</td>
	      <td><input type=\"text\" name=\"paht[".$g->garantieid."]\" value=\"".$g->paht."\" size=\"5\" readonly></td>
	      <td><input type=\"text\" name=\"prix[".$g->garantieid."]\" value=\"".$g->prix."\" size=\"5\" readonly></td>
	      <td><input type=\"text\" name=\"ppi[".$g->garantieid."]\" value=\"".$g->ppi."\" size=\"5\" readonly></td>
	      <td><input type=\"checkbox\" name=\"garchk[".$g->garantieid."]\"></td>
	    </tr>";
	    
	    $garobjlist[$g->garantieid]=$g;
	}
	$garlist.="<tr>
	<td colspan=\"9\" class=\"supp\"><input type=\"checkbox\" name=\"supp_before\"> Supprimer les garanties déjà incluses</td>
	</tr>
	</table>";
    }else
	$garlist="<p>Aucune garantie disponible</p>";

    $artlist="<br><h2>Produits</h2>
    <table class=\"tbl\">";
    $sql="SELECT artid,artcode,artcode_eurocave,active,libelleFR,(SELECT COUNT(artid) FROM article_garantie WHERE artid=a.artid) AS contrats FROM article a INNER JOIN article_marque USING(artid) WHERE marqueid=".$marqueid." AND typearticleid=".$typearticleid." AND (SELECT COUNT(*) FROM article_lot WHERE lotid=a.artid)=0";
    $req=$DB_site2->query($sql);
    
    $artlist.="<thead>
    <tr>
      <td colspan=\"4\">".$DB_site2->num_rows($req)." résultats</td>
      <td><a href=\"javascript:checkall(true)\">Tout cocher</a><br><a href=\"javascript:checkall(false)\">Tout décocher</a></td>
      <td><input type=\"button\" onclick=\"updateall()\" value=\"Mettre à jour\"></td>
    </tr>
    </thead>";
    
    
    while($art=$DB_site2->fetch_array($req)){
	
	$garartlist="<table>";
	$garantiesInArt=DAOgarantie::checkGarantiesArt($DB_site2,'FR',$art['artid']);
	if($garantiesInArt!=0){
	    foreach($garantiesInArt as $g){
		$garobj=$garobjlist[$g['garantieid']];
		$style="";
		if(is_null($garobj)){
		    $garobj=new garantie($DB_site2,"FR",$g['garantieid']);
		    $style="garerror";
		}
		$garartlist.="<tr class=\"".$style."\">
		  <td class=\"garantieid\">".$garobj->garantieid."</td>
		  <td>".$garobj->duree." an".($garobj->duree>1?"s ":" ").$garobj->getTypeGarantie()." ".$garobj->fournisseur."</td>
		  <td class=\"".($g['paht']!=$garobj->paht?"diffprix":"")."\">".$g['paht']."</td>
		  <td class=\"".($g['prix']!=$garobj->prix?"diffprix":"")."\">".$g['prix']."</td>
		  <td class=\"".($g['ppi']!=$garobj->ppi?"diffprix":"")."\">".$g['ppi']."</td>
		</tr>";
	    }
	}
	$garartlist.="</table>";
    
	$contrats=($art['contrats']==0?1:$art['contrats']);
    
	$artlist.="<tr>
	    <td><img src=\"../configurations/".$host."/images/produits/br/".$art['artid'].".jpg\" width=\"50\"></td>
	    <td><a href=\"ajout_produit.php?artid=".$art['artid']."&prec=modif\" target=\"_blank\">".$art['artcode']."</a><br><i>".$art['artcode_eurocave']."</i><br>".$active[$art['active']]."</td>
	    <td>".$art['libelle']."</td>
	    <td>".$contrats." contrat".($contrats>1?"s":"")."</td>
	    <td>".$garartlist."</td>
	    <td><input type=\"checkbox\" name=\"chk[".$art['artid']."]\"></td>
	</tr>";
    }
    $artlist.="</table>";
}

print "<h1>Modification groupée garantie</h1>";

print <<<JS
<script language="javascript">
function checkall(state){
     var chklist=document.getElementsByTagName("input");
     for(i=0;i<chklist.length;i++)
	if(chklist[i].type=="checkbox" && chklist[i].name.substr(0,3)=="chk")
	    chklist[i].checked=state;
}

function updateall(){
     var chklist=document.getElementsByTagName("input");
     var chknb=0;
     var chkgarnb=0;
     for(i=0;i<chklist.length;i++)
	if(chklist[i].type=="checkbox"){
	  if(chklist[i].name.substr(0,3)=="chk" && chklist[i].checked==true)
	    chknb++;
	    
	  if(chklist[i].name.substr(0,6)=="garchk" && chklist[i].checked==true)
	    chkgarnb++;
     }
	    
     if(chknb==0){
	alert("Aucun article n'est sélectionné");
	return;
     }
     if(chkgarnb==0 && !document.getElementsByName("supp_before")[0].checked){
	alert("Aucune garantie n'est sélectionnée");
	return;
     }
     
     document.forms.updateform.submit();
}
</script>
JS;

print <<<CSS
<style>
.garantieid { color:#0088FF; font-weight:bold; }
.garerror td { color:red; }
.diffprix { font-weight:bold; }
.tbl { border:solid 1px black; }
.tbl thead { font-weight:bold; }
.tbl td { border-bottom:solid 1px #CCC; }
.supp { text-align:right; color:red; }
</style>
CSS;

print <<<HTML
$updateresult
<form method="post">
Marque : $marquefield
 Type : $typefield
 <input type="submit" value="OK">
</form>
HTML;
    
print <<<HTML
<form name="updateform" method="post">
<input type="hidden" name="marqueid" value="$marqueid">
<input type="hidden" name="typearticleid" value="$typearticleid">
$garlist
$artlist
</form>
HTML;
    
include "./footer.php";
    
?>