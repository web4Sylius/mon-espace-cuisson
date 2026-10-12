<?php

require "admin_global.php";
include "./header.php";
include "./menu.php";

include "./fonctions.php";  

include("../class/stock.class.php");

echo "<h1>Catalogue</h1>" ;

$filter_langue=count(array_diff($langues,$langue_access))>0;

if( isset( $artid ) && $artid != ""){
	if(isset($_GET['site_code'])){
	    $site_code=$_GET['site_code'];
	    if( $action == "cdc_oui" )
	      // Activer un coup de coeur
		$DB_site2->query( "INSERT INTO coupdecoeur ( artid, site_code ) VALUES ( '$artid', '$site_code' )" );
	    elseif( $action == "cdc_non" )
	      // Désactiver un coup de coeur
		$DB_site2->query( "DELETE FROM coupdecoeur WHERE artid='$artid' AND site_code='".$site_code."'" );
	    
	    
	    if( $action == "promo_oui" )
	      // Activer une promo
		$DB_site2->query( "INSERT INTO promo ( artid, site_code ) VALUES ( '$artid', '$site_code' )" );
	    elseif( $action == "promo_non" )
	      // Désactiver une promo
		$DB_site2->query( "DELETE FROM promo WHERE artid='$artid' AND site_code='".$site_code."'" );
	    
	    if($action=="idkdos_oui")
		$DB_site2->query("INSERT INTO idkdos SET artid=".$artid.", site_code='".$site_code."'");
	    elseif($action=="idkdos_non")
		$DB_site2->query("DELETE FROM idkdos WHERE artid=".$artid." AND site_code='".$site_code."'");
	}

	if($action=="selection_oui")
	    $DB_site2->query("DELETE FROM article_deselection WHERE artid=".$artid);
	elseif($action=="selection_non")
	    $DB_site2->query("INSERT INTO article_deselection SET artid=".$artid);

	if($action=="secondchoix_non")
	    $DB_site2->query("DELETE FROM secondchoix WHERE artid=".$artid);
	elseif($action=="secondchoix_oui")
	    $DB_site2->query("INSERT INTO secondchoix SET artid=".$artid);

	if(isset($_GET['pro'])){
	    if($_GET['pro']==0) $_GET['pro']=1;
	    $DB_site2->query("UPDATE article SET pro='".intval($_GET['pro'])."' WHERE artid=".$_GET['artid']);
	}
}
//MISE A JOUR POSITION
if (isset ($action) and $action == "maj"){
	$i=0;
	$menu_appartenance = mysqli_real_escape_string($DB_site2->link_id,$_GET['menu_appartenance']);
	$parentid = mysqli_real_escape_string($DB_site2->link_id,$_GET['parentid']);
	$sql_parent="";
	if($parentid != 0) $sql_parent = "AND parentid='$parentid'";
	$categ = $DB_site2->query("SELECT catid FROM categorie WHERE menu_appartenance='$menu_appartenance' $sql_parent ORDER BY libelleFR ASC");
	while($row = $DB_site2->fetch_array($categ)){
		$DB_site2->query("UPDATE categorie SET position='$i' WHERE catid =".$row['catid']);
		$i++;
	}
	echo "<script language=\"javascript\">location.href=\"catalogue.php?catid=$parentid#menu".$position['menu_appartenance']."\";</script>";
}
// MONTER D'UNE POSITION 
if (isset ($action) and $action == "up")
    {
    $position=$DB_site2->query_first("SELECT position,menu_appartenance FROM categorie WHERE catid = '$cat'");                        
    $nouvelle_position_marque = $position[0] - 1 ;
    $position2=$DB_site2->query_first("SELECT catid FROM categorie WHERE position = '$nouvelle_position_marque' and parentid = '$parent' AND menu_appartenance=".$position['menu_appartenance']);
    $DB_site2->query("UPDATE categorie SET position = '$nouvelle_position_marque' WHERE catid = '$cat'");
    $DB_site2->query("UPDATE categorie SET position = '$position[0]' WHERE catid = '$position2[catid]'");
    echo "<script language=\"javascript\">location.href=\"catalogue.php?catid=$parent#menu".$position['menu_appartenance']."\";</script>";
    }

// DESCENDRE D'UNE POSITION 
if (isset ($action) and $action == "down")
    {
    $position=$DB_site2->query_first("SELECT position,menu_appartenance FROM categorie WHERE catid = '$cat'");                        
    $nouvelle_position_marque = $position[0] + 1 ;
    $position2=$DB_site2->query_first("SELECT catid FROM categorie WHERE position = '$nouvelle_position_marque' and parentid = '$parent' AND menu_appartenance=".$position['menu_appartenance'] );
    //echo "SELECT position,menu_appartenance FROM categorie WHERE catid = '$cat' <br> SELECT catid FROM categorie WHERE position = '$nouvelle_position_marque' and parentid = '$parent' AND menu_appartenance=".$position['menu_appartenance']."<br><br><br>";
    //echo "UPDATE categorie SET position = '$nouvelle_position_marque' WHERE catid = '$cat' <br> UPDATE categorie SET position = '$position[0]' WHERE catid = '$position2[catid]'";
    $DB_site2->query("UPDATE categorie SET position = '$nouvelle_position_marque' WHERE catid = '$cat'");
    $DB_site2->query("UPDATE categorie SET position = '$position[0]' WHERE catid = '$position2[catid]'");
    echo "<script language=\"javascript\">location.href=\"catalogue.php?catid=$parent#menu".$position['menu_appartenance']."\";</script>";
    }

if ($catid=="" || !isset($catid))
{
    $catid="0";
    $parentid="0";
}
else
    $parentid=$catid;

$i=0;
$tabcategorie=array();

// AFFICHAGE BARRE NAVIGATION
/*$recherche="
<tr><td>
		<table class=\"form_rech_article\">
		<tr>
		<td>Recherche : </td>
		<td></td>
		<td><img src=\"images/en_cours.png\" title=\"En cours de création sur site $site_code\" width=\"30\"></td>
		<td><img src=\"images/new.png\" title=\"Nouveau à contrôler\" width=\"30\"></td>
		<td><img src=\"images/visible.png\" title=\"Visible sur site $site_code\" width=\"30\"></td>
		<td><img src=\"images/invisible.png\" title=\"Invisible sur site $site_code\" width=\"30\"></td>
		<td><img src=\"images/supprime.png\" title=\"Supprimé sur site $site_code\" width=\"30\"></td>
		<td><img src=\"images/archive_temp.png\" title=\"Archivé temporairement sur site $site_code\" width=\"30\"></td>
		<td><img src=\"images/archive.png\" title=\"Archivé définitivement sur site $site_code\" width=\"30\"></td>
		</tr>
		<tr>
		<td><input class=\"bouton1\" type=\"text\" name=\"recherche\" size=\"10\"></td>
		<td><input type=\"checkbox\" onclick=\"tout_cocher()\"></td>
		<td><input type=\"checkbox\" name=\"active[]\" value=\"6\"></td>
		<td><input type=\"checkbox\" name=\"active[]\" value=\"4\"></td>
		<td><input type=\"checkbox\" name=\"active[]\" value=\"1\" checked></td>
		<td><input type=\"checkbox\" name=\"active[]\" value=\"0\" checked></td>
		<td><input type=\"checkbox\" name=\"active[]\" value=\"2\"></td>
		<td><input type=\"checkbox\" name=\"active[]\" value=\"5\"></td>
		<td><input type=\"checkbox\" name=\"active[]\" value=\"3\"></td>
		</tr>
		<tr>
		<td><img src=\"./images/rechercher.gif\" onclick=\"rechercher();\" style=\"cursor:hand\" align=\"absmiddle\"></td>
		<td>Tout<br>cocher </td>
		<td>En<br>création</td>
		<td>A<br>contrôler</td>
		<td>Actifs</td>
		<td>Actifs<br>invisibles</td>
		<td>Supprimés</td>
		<td>Archivés<br>temporairement</td>
		<td>Archivés<br>définitivement</td>
		</tr>
		</table></td>
		</tr>";

*/
?>
<script language="javascript">
     
     function rechercher(){
          if(document.formu.recherche.value != ''){
               document.formu.submit();
          }
     }

     function scrolltop(){
         document.body.scrollTop = <?php echo intval($_GET['scrolltop']); ?>;
     }
     
     function upd_param(params){
         document.location = 'catalogue.php?catid=<?php echo $catid; ?>'+params+'&scrolltop='+document.body.scrollTop;
     }
     
     window.addEventListener("load", scrolltop, false);
     
</script>

<?php
// AFFICHAGE BARRE RECHERCHE
include_once(BACK_PATH.'/includes/catalogue_recherche.php');
/*echo "<form name=\"formu\" method=\"post\" action=\"rechercher.php\">";
echo $recherche;
echo "</form>
<script language=\"javascript\">
	function rechercher(){
			document.formu.submit();
    }
	
    function scrolltop(){
		var scrolltop=".intval($_GET['scrolltop']).";
		if(scrolltop>0) document.body.scrollTop=scrolltop;
    }
	
    function upd_param(params){
		document.location='catalogue.php?catid=$catid".(isset($_GET['filter_site'])?"&filter_site=".$_GET['filter_site']:"")."'+params+'&scrolltop='+document.body.scrollTop;
    }

    function tout_cocher() {
    		const inputs = formu.querySelectorAll('input');
    	const nb_check = inputs.length;
    	const nb_check2 = formu.querySelectorAll('input:checked').length;
    	if((nb_check - 2) ===  nb_check2) {
    		inputs.forEach((input) => {
        		input.checked = false;
    		});
    	}
    	else {
    		inputs.forEach((input) => {
        		input.checked = true;
    		});
    	}
    }
	
    window.addEventListener(\"load\", scrolltop, false);
</script>
";

*/
$show_sites=$sites;
if(isset($_GET['filter_site']) && $_GET['filter_site']!="") $show_sites=array($_GET['filter_site']=>$sites[$_GET['filter_site']]);

$testcat=array("parentid"=>$parentid);
$arborescence=array();
if($parentid>0){
    do{
	$sql="SELECT catid,parentid,menu_appartenance,libelleFR FROM categorie WHERE catid=".$testcat['parentid'];
	$testcat=$DB_site2->query_first($sql);
	$arborescence[]=$testcat;
    }while($testcat['menu_appartenance']==0 && $testcat['parentid']>0);
}

$arborescence=array_reverse($arborescence);

$menus=array();
    if($parentid>0)
      echo "<p><a href=ajouter_categorie.php?parentid=$catid>Ajouter un sous-rayon <img src=\"images/ajouter.gif\" align=\"absmiddle\"></a></p>";
    
    print "<table class=\"table_modules_boutiques\">";
    
    if($testcat['menu_appartenance']>0 || $parentid==0){
        $sqlmenu="SELECT * FROM menus ".($parentid>0?"WHERE idmenu=".intval($testcat['menu_appartenance']):"")." ORDER BY position";

        $rmenu=$DB_site2->query($sqlmenu);
        while($menu=$DB_site2->fetch_array($rmenu)){
            $menus[]=$menu;
        }
    }else{
        $menus[]=array("idmenu"=>0,"libelleFR"=>"Catégories invisibles sur le site");
    }
    
    foreach($menus as $menu){
	print "<tr>
		<td colspan=\"2\" height=\"30\" id=\"menu".$menu['idmenu']."\"><a href=\"catalogue.php\">Menus</a> &gt; <a href=\"catalogue.php#menu".$menu['idmenu']."\">".$menu['libelleFR']."</a>";
	
	if($parentid==0){
	    $sql=" SELECT * FROM categorie WHERE menu_appartenance='".$menu['idmenu']."' ORDER BY position, libelle".$langue." ASC ";
	    print "&nbsp;&nbsp;&nbsp;&nbsp;<a href=\"catalogue.php?action=maj&menu_appartenance=".$menu['idmenu']."&parentid=".$parentid."#menu".$menu['idmenu']."\">MAJ position</a>";
	}else{
	    foreach($arborescence as $parentcat)
		print " &gt; <a href=\"catalogue.php?catid=".$parentcat['catid']."\">".$parentcat['libelleFR']."</a>";
	    $sql="SELECT * FROM categorie WHERE parentid=".$parentid." ORDER BY position";
	    print "&nbsp;&nbsp;&nbsp;&nbsp;<a href=\"catalogue.php?action=maj&menu_appartenance=0&parentid=".$parentid."#menu".$menu['idmenu']."\">MAJ position</a>";
	}
	print "</td>
	    </tr>";

	$cats=$DB_site2->query($sql);
	$nb_cat = $DB_site2->num_rows($cats);
	$i_cat = 1;
	while ($cat=$DB_site2->fetch_array($cats)) {
	    $limiteatteinte=0;
	    $nb_produits=compter_produits($cat['catid']);
	    if($affichagenombrearticle==1) {
		$nb_produit_aff_cat="($nb_produits)";
	    }
	    $i=0;
	    $sscatbit="";
	    $virgule="";
	    $requete=" SELECT * FROM categorie WHERE parentid='$cat[catid]' ORDER BY libelle".$langue." ASC ";
	    $sscats=$DB_site2->query($requete);
	    while ($sscat=$DB_site2->fetch_array($sscats)) {
			$nb_produitsscat=compter_produits($sscat['catid']);
			$i++;
			if ($i<=$limitcategorie) {
			    if($affichagenombrearticle==1) {
					$nb_produit_aff_ss_cat="($nb_produitsscat)";
			    }
			    $sscatbit.="$virgule <a href=\"catalogue.php?catid=$sscat[catid]\">$sscat[$libelle]</a> $nb_produit_aff_ss_cat";
			    $virgule=",";
			}else{
			    if ($limiteatteinte!=1){
					$sscatbit.="$virgule <a href=\"catalogue.php?catid=$cat[catid]\">...</a>";
			    }
			    $limiteatteinte=1;
			}
	    }
	    $rowalt="td_".getrowbg();

	    if ($nb_cat > 1 && !$filter_langue)
		{
		if ($i_cat == 1) // Première marque
		    {
		    $positioncat = "<img src=\"images/vide.gif\" height=\"7\">
					<a href=\"catalogue.php?action=down&cat=$cat[catid]&parent=$parentid\">
					<img src=\"images/descendre.gif\" alt=\"Descendre\" title=\"Descendre\" height=\"7\" border=\"0\"></a>";
		    }
		elseif ($i_cat == $nb_cat) // Dernière marque
		    {
		    $positioncat = "<a href=\"catalogue.php?action=up&cat=$cat[catid]&parent=$parentid\">
					<img src=\"images/monter.gif\" alt=\"Monter\" title=\"Monter\" height=\"7\" border=\"0\"></a>
					<img src=\"images/vide.gif\" height=\"7\">";
		    }
		else
		    {
		    $positioncat = "<a href=\"catalogue.php?action=up&cat=$cat[catid]&parent=$parentid\">
					<img src=\"images/monter.gif\" alt=\"Monter\" title=\"Monter\" height=\"7\" border=\"0\"></a>
					<a href=\"catalogue.php?action=down&cat=$cat[catid]&parent=$parentid\">
					<img src=\"images/descendre.gif\" alt=\"Descendre\" title=\"Descendre\" height=\"7\" border=\"0\"></a>";
		    }
		}
		$i_cat++ ;
		$catimg="";
		$catimgpath="../configurations/".$host."/images/categories/new/".$cat['catid'].".jpg";
		if(file_exists($catimgpath)){
                    $catimg="<a href=\"".$catimgpath."\" target=\"_blank\"><img src=\"".$catimgpath."\" width=\"50\"></a>";
		}
	    
	    echo "<tr valign=\"center\">
		    <td class=\"$rowalt\">$positioncat $catimg</td>
		    <td class=\"$rowalt\" width=\"50%\"><b><a href=\"catalogue.php?catid=$cat[catid]\">$cat[$libelle]</a></b>
					$nb_produit_aff_cat<br>
					<table><tr><td>$sscatbit</td></tr></table>
			</td>";
	    
	    echo "<td class=\"$rowalt\">
	      ".$cat['onglet']."
	    </td>";
	    
	    echo "<td align=\"center\" valign=\"middle\" class=\"$rowalt\">
	    <a href=\"modifier_categorie.php?catid=$cat[catid]\" title=\"Editer\"><img src=\"images/editer.gif\"></a></td>";
	    
		
	    if(!$filter_langue) {
	      echo "<td align=\"center\" valign=\"middle\" class=\"$rowalt\"><a href=deplacer_categorie.php?catid=$cat[catid] title=\"Déplacer\"><img src=\"images/deplacer.gif\"></a></td>";
	    }
		
		echo "<td class=\"$rowalt\"><a href=\"cloudflare.php?action=deletecateg&mode=mini&catid=".$cat['catid']."\" target=\"_blank\" style=\"color:$linkcolor;\">[retirer cache]</a></td>";
	    
	    if($parentid==0){
		$promocat=$DB_site2->query_first("SELECT catid FROM categorie_promo WHERE catid=".$cat['catid']);
		$linkcolor=($promocat==false?"red":"green");
		echo "<td class=\"$rowalt\"><a href=\"categorie_promo.php?catid=".$cat['catid']."\" style=\"color:$linkcolor;\">Page promos</a></td>";
		
		$promocat=$DB_site2->query_first("SELECT catid FROM categorie_idkdos WHERE catid=".$cat['catid']);
		$linkcolor=($promocat==false?"red":"green");
		echo "<td class=\"$rowalt\"><a href=\"categorie_idkdos.php?catid=".$cat['catid']."\" style=\"color:$linkcolor;\">Idées cadeaux</a></td>";
		
		$archcat=$DB_site2->query_first("SELECT catid FROM categorie_archive WHERE catid=".$cat['catid']);
		$linkcolor=($archcat==false?"red":"green");
		echo "<td class=\"$rowalt\"><a href=\"categorie_archive.php?catid=".$cat['catid']."\" style=\"color:$linkcolor;\">Page archives</a></td>";
		
		
		$gammecolor=array();
		/*$reqgamme=$DB_site2->query("SELECT gamme FROM categorie_gamme WHERE catid=".$cat['catid']);
		while($gammecat=$DB_site2->fetch_array($reqgamme)){
            $gammecolor[$gammecat['gamme']]=true;
        }*/
        	/* echo "<td class=\"$rowalt\">
                <a href=\"categorie_gamme.php?catid=".$cat['catid']."&gamme=access\" style=\"color:".($gammecolor['access']?"green":"red").";\">Access</a>
                <a href=\"categorie_gamme.php?catid=".$cat['catid']."&gamme=premium\" style=\"color:".($gammecolor['premium']?"green":"red").";\">Premium</a>
                </td>";*/
            echo "<td class=\"$rowalt\"></td>";    
	    }
	    
	echo "</tr>";
	}
    }
    if(count($menus)==0)
	print "<tr>
		<td colspan=\"2\" height=\"30\"><a href=\"catalogue.php\">Catalogue</a></td>
	</tr>";
	
    
    
    if($parentid==0){
	$req=$DB_site2->query("SELECT * FROM categorie WHERE parentid=0 AND menu_appartenance=0 ORDER BY libelle".$langue);
	if($DB_site2->num_rows($req)>0){
	    print "<tr>
		  <td colspan=\"2\" height=\"30\" id=\"menu0\">Catégories invisibles sur le site</td>
		</tr>";
		
	    while($cat=$DB_site2->fetch_array($req)){
		$inmenu=false;
		$sscatbit="";
		$virgule="";
		$sscats=$DB_site2->query("SELECT * FROM categorie WHERE parentid='$cat[catid]' ORDER BY libelle".$langue." ASC");
		while ($sscat=$DB_site2->fetch_array($sscats)) {
		    $nb_produitsscat=compter_produits($sscat['catid']);
		    $i++;

		    $sscatbit.="$virgule <a href=\"catalogue.php?catid=$sscat[catid]\">$sscat[$libelle]</a> ($nb_produitsscat)";
		    $virgule=",";
		    if($sscat['menu_appartenance']>0) $inmenu=true;
		}
		
		if(!$inmenu){
		    $rowalt="td_".getrowbg();
		    $nb_produitcat=compter_produits($cat['catid']);
		    print "<tr>
			<td class=\"".$rowalt."\"></td>
			<td class=\"".$rowalt."\" width=\"50%\"><b><a href=\"catalogue.php?catid=$cat[catid]\">$cat[$libelle]</a></b> ($nb_produitcat)<div>$sscatbit</div></td>
			<td class=\"$rowalt\">".$cat['onglet']."</td>
			<td align=\"center\" valign=\"middle\" class=\"$rowalt\"><a href=\"modifier_categorie.php?catid=$cat[catid]\" title=\"Editer\"><img src=\"images/editer.gif\"></a></td>
			<td align=\"center\" valign=\"middle\" class=\"$rowalt\"><a href=deplacer_categorie.php?catid=$cat[catid] title=\"Déplacer\"><img src=\"images/deplacer.gif\"></a></td>
			<td class=\"$rowalt\" colspan=\"4\">&nbsp;</td>
		    </tr>";
		}
	    }
	}
    }
    
    echo "</table><br><br>";
// }
// else
//     {
//     echo "<p><a href=\"ajouter_categorie.php?parentid=$catid\">Ajouter un sous-rayon <img src=\"images/ajouter.gif\" align=\"absmiddle\"></a></p>
//         <table class=\"table_modules_boutiques\">
//             <tr>
//                 <td align=\"center\"><b>Il n'y a aucun sous-rayon dans ce rayon</b></td>
//             </tr>
//         </table><br>";
//     }

$imghost="../";

$stock=new stock($DB_site2);

// AFFICHAGE DES PRODUITS

$active="AND active IN ('0','1', '4','6')";
if(isset($_GET['all'])) $active="";
$douzemois=time()-365*86400;
$nb_article=$DB_site2->query_first("select count(*) from article where catid=$parentid AND catid>0 $active ORDER BY artcode");
if ($nb_article[0]>0){

    // Vérification module coups de coeur
    if( in_array( "21", $modules ) )
        $module_coupdecoeur = 1;
    else
        $module_coupdecoeur = 0;

    // Vérification module promotions
    if( in_array( "5", $modules ) ) {
        $module_promotions = 1;
        $datejour = date("Y") ."-". date("m") . "-" . date("d");
    } else
        $module_promotions = 0;

    $order="artcode";
    if(isset($_GET['order'])) $order=$_GET['order'];
    $sens="ASC";
    if(isset($_GET['sens'])) $sens=$_GET['sens'];
    $sensinv="DESC";
    $sensicon="&#9650;";
    if($sens=="DESC"){ $sensinv="ASC"; $sensicon="&#9660;"; }

    $sql="SELECT a.* FROM article a WHERE a.catid =$parentid $active ORDER BY artcode";
    $articles=$DB_site2->query($sql);
    $positions = "";
    $tr=array();
  
    $langue_url=$DB_site2->query_first("SELECT * FROM traduction WHERE path='/site/url'");
    $actives=array(0=>"<font color=\"red\">Invisible</font>",1=>"<font color=\"green\">Visible</font>",2=>"<font color=\"red\">Supprimé</font>",3=>"<font color=\"#FFBB00\">Archivé définitivement</font>");

    $tdcdc=$tdpromo=$tdidkdos=$tdvisible=array();
    $req = $DB_site2->query( "SELECT coupdecoeur.* FROM coupdecoeur INNER JOIN article USING(artid) WHERE catid = '$parentid'" );
    while($rs_coupdecoeur=$DB_site2->fetch_array($req))
	if( $rs_coupdecoeur['coupdecoeurid'] != "" ) 
	    $tdcdc[$rs_coupdecoeur['site_code']][$rs_coupdecoeur['artid']]=true;

    
    $req = $DB_site2->query( "SELECT promo.* FROM promo INNER JOIN article USING(artid) WHERE catid = '$parentid' AND dbt_promo=0 OR ".time()." BETWEEN dbt_promo AND fin_promo" );
    while($rs_promotion=$DB_site2->fetch_array($req))
	if( $rs_promotion['promoid'] != "" ) 
	    $tdpromo[$rs_promotion['site_code']][$rs_promotion['artid']]=true;
    

    $req = $DB_site2->query( "SELECT idkdos.* FROM idkdos INNER JOIN article USING(artid) WHERE catid = '$parentid'" );
    while($rs_idkdos=$DB_site2->fetch_array($req))
	if( $rs_idkdos['id'] != "" ) 
	    $tdidkdos[$rs_idkdos['site_code']][$rs_idkdos['artid']]=true;

    $req = $DB_site2->query("SELECT a_s.* FROM article_site a_s INNER JOIN article USING(artid) WHERE catid = '$parentid'");
    while($as=$DB_site2->fetch_array($req))
	$tdvisible[$as['site_code']][$as['artid']]=$as['active'];

    while( $article=$DB_site2->fetch_array($articles) )
    {
		$artcode=$article['artcode'];
			if ($artcode != "")
				{
				$ref_article = "<i>Réf $artcode</i> ($article[artcode_eurocave]):<br>";
				}
				else
				{
				$ref_article = "";
				}
			$rowalt="td_promos_".getrowbg();
			
			$reappro="";
		if($article['date_appro']>time()) $reappro="<p style=\"color:blue;margin:0;text-align:right;\">Réappro le ".date("d/m/Y",$article['date_appro']."</p>");

		$redirect="";
		if($article['redirect']!=""){
			$redirect_art=$DB_site2->query_first("SELECT artid,artcode,active FROM article WHERE artid=".$article['redirect']);
			$redirect="<a href=\"ajout_produit.php?artid=".$redirect_art['artid']."&prec=modif\"><img src=\"/configurations/".$host."/images/produits/br/".$redirect_art['artid'].".jpg\" height=\"80\"><br>".$redirect_art['artcode']."</a><br>".$actives[$redirect_art['active']];
		}
		$actif_auto="";
		$article_visible=$DB_site2->query_first("SELECT date,active FROM article_visible WHERE artid='".$article['artid']."' AND date>UNIX_TIMESTAMP(NOW()) LIMIT 1");
		if($article_visible!=false) $actif_auto="<br> &#10227; ".$actives[$article_visible['active']]." le ".date("d/m/Y",$article_visible['date']);
		
			$line="<tr>
			<td class=\"$rowalt\"><img src=\"".$imghost."configurations/".$host."/images/produits/br/$article[artid].jpg\" height=\"80\"></td>
			<td class=\"$rowalt\" width=\"50%\">$ref_article $article[$libelle] ".$actif_auto.$reappro."</td>
			<td class=\"$rowalt\" style=\"".($article['active']==1?"opacity:0.6":"")."\">$redirect</td>
			<td valign=\"middle\" align=\"center\" class=\"$rowalt\"><a href=\"ajout_produit.php?artid=$article[artid]&prec=modif\" title=\"Editer\"><img src=\"images/editer.gif\"></a></td>
			<td valign=\"middle\" align=\"center\" class=\"$rowalt\"><a href=\"deplacer_produit.php?artid=$article[artid]\" title=\"Déplacer\"><img src=\"images/deplacer.gif\"></a></td>
			<td valign=\"middle\" align=\"center\" class=\"$rowalt\"><a href=\"copier_produit.php?artid=$article[artid]\" title=\"Copier\"><img src=\"images/copier.gif\"></a></td>
			<td valign=\"middle\" align=\"center\" class=\"$rowalt\">
				<form target=\"_blank\" method=\"post\" action=\"cloudflare.php?action=deletearticle&amp;mode=mini\">
					<input type=\"hidden\" name=\"artid\" value=\"".$article[artid]."\">
					<input type=\"submit\" value=\"décacher\">
				</form>
			</td>";
		/*
		<form target="_blank" method="post" action="cloudflare.php?action=deletearticle&amp;mode=mini">
			<input type="hidden" name="artid" value="13172">
			<input type="submit" value="retirer du cache">
		</form>
		*/

		$rs_selection = $DB_site2->query_first( "SELECT * FROM article_deselection WHERE artid = '$article[artid]'" );
		if( $rs_selection['id'] != "" ) {
			$tdselection="<img src=\"images/selection_rouge.png\" title=\"Mettre dans la sélection\" onclick=\"upd_param('&action=selection_oui&artid=$article[artid]')\" class=\"cursor\">";
		} else {
			$tdselection="<img src=\"images/selection_vert.png\" title=\"Enlever de la sélection\" onclick=\"upd_param('&action=selection_non&artid=$article[artid]')\" class=\"cursor\">";
		}

		$secondchoix=$DB_site2->query_first("SELECT * FROM secondchoix WHERE artid=".$article['artid']);
		if($secondchoix['id']!="")
			$tdsecondchoix="<a href=\"javascript:upd_param('&action=secondchoix_non&artid=$article[artid]')\" style=\"color:green;\">2<sup>nd</sup> choix</a>";
		else
			$tdsecondchoix="<a href=\"javascript:upd_param('&action=secondchoix_oui&artid=$article[artid]')\" style=\"color:red;\">2<sup>nd</sup> choix</a>";

		list($pro,$part)=str_split(str_pad(decbin($article['pro']),2,0,STR_PAD_LEFT));
		$tdpro="<a href=\"javascript:void(0);#\" onclick=\"upd_param('&pro=".intval(2*abs($pro-1)+$part)."&artid=".$article['artid']."')\" style=\"color:".($pro==1?"green":"red").";\">Pro</a>";

		foreach($show_sites as $site_code=>$site) {
			$line.="<td class=\"$rowalt\" align=\"center\">";

			if(isset($tdvisible[$site_code][$article['artid']]))
			switch($tdvisible[$site_code][$article['artid']]){
				case 0:
				$line.="<img src=\"images/invisible.png\" title=\"Invisible sur site $site_code\" width=\"30\">";
				break;

				case 1:
				$line.="<img src=\"images/visible.png\" title=\"Visible sur site $site_code\" width=\"30\">";
				break;

				case 2:
				$line.="<img src=\"images/supprime.png\" title=\"Supprimé sur site $site_code\" width=\"30\">";
				break;
				
				case 3:
				$line.="<img src=\"images/archive.png\" title=\"Archivé définitivement sur site $site_code\" width=\"30\">";
				break;

				case 4:
				$line.="<img src=\"images/new.png\" title=\"Nouveau à contrôler\" width=\"30\">";
				break;

				case 5:
				$line.="<img src=\"images/archive_temp.png\" title=\"Archivé temporairement sur site $site_code\" width=\"30\">";
				break;

				case 6:
				$line.="<img src=\"images/en_cours.png\" title=\"En cours de création sur site $site_code\" width=\"30\">";
				break;
			}
			else
			$line.="<span style=\"font-size:16px;\" title=\"Non configuré sur site $site_code\">?</span>";
			

		
			if($tdpromo[$site_code][$article['artid']])
			$line.="<br><img src=\"images/promotion_vert.gif\" title=\"Enlever des promo site $site_code\" onclick=\"upd_param('&action=promo_non&artid=$article[artid]&site_code=$site_code')\" class=\"cursor\">";
			else
			$line.="<br><img src=\"images/promotion.gif\" title=\"Ajouter une promotion site $site_code\" onclick=\"upd_param('&action=promo_oui&artid=$article[artid]&site_code=$site_code')\" class=\"cursor\">";
			
			/*
			if($tdidkdos[$site_code][$article['artid']])
			$line.="<br><img src=\"images/kdo_vert.gif\" title=\"Enlever des idées cadeau site $site_code\" onclick=\"upd_param('&action=idkdos_non&artid=$article[artid]&site_code=$site_code')\" class=\"cursor\">";
			else
			$line.="<br><img src=\"images/kdo_rouge.gif\" title=\"Ajouter une idée cadeau site $site_code\" onclick=\"upd_param('&action=idkdos_oui&artid=$article[artid]&site_code=$site_code')\" class=\"cursor\">";
			*/

			$line.="</td>";
		}
		  
		$line.="<td class=\"$rowalt\" align=\"center\">".$tdsecondchoix."</td>";

		$sqlfilter="";
		if(isset($_GET['filter_site'])) $sqlfilter=" AND site_code='".$_GET['filter_site']."'";
		$ventes=$DB_site2->query_first("SELECT SUM(prix/(1+tva/100)) AS prix,SUM(qte) AS qte FROM lignefacture lf INNER JOIN facture ON (facturid = factureid) WHERE artid=".$article['artid']." AND valide IN (1, 5, 7, 8)$sqlfilter GROUP BY artid");
		$venteslot=$DB_site2->query_first("SELECT SUM(prix/(1+tva/100)) AS prix,SUM(qte) AS qte FROM lignefacturediss lf INNER JOIN facture ON (facturid = factureid) WHERE artid=".$article['artid']." AND valide IN (1, 5, 7, 8)$sqlfilter GROUP BY artid");
		$ventes12=$DB_site2->query_first("SELECT SUM(prix/(1+tva/100)) AS prix,SUM(qte) AS qte FROM lignefacture lf INNER JOIN facture ON (facturid = factureid) WHERE artid=".$article['artid']." AND valide IN (1, 5, 7, 8) AND heurefacture>".$douzemois.$sqlfilter." GROUP BY artid");
		$venteslot12=$DB_site2->query_first("SELECT SUM(prix/(1+tva/100)) AS prix,SUM(qte) AS qte FROM lignefacturediss lf INNER JOIN facture ON (facturid = factureid) WHERE artid=".$article['artid']." AND valide IN (1, 5, 7, 8) AND heurefacture>".$douzemois.$sqlfilter." GROUP BY artid");
		$ventesmp=$DB_site2->query_first("SELECT SUM(prix/(1+tva/100)) AS prix,SUM(qte) AS qte FROM lignefacture lf INNER JOIN facture ON (facturid = factureid) WHERE artid=".$article['artid']." AND valide IN (1, 5, 7, 8) AND provenance='mp'$sqlfilter GROUP BY artid");
		$venteslotmp=$DB_site2->query_first("SELECT SUM(prix/(1+tva/100)) AS prix,SUM(qte) AS qte FROM lignefacturediss lf INNER JOIN facture ON (facturid = factureid) WHERE artid=".$article['artid']." AND valide IN (1, 5, 7, 8) AND provenance='mp'$sqlfilter GROUP BY artid");
		$item=$stock->in_stock($article['artid']);
		$qtedeb=intval($ventes['qte']+$venteslot['qte']);
		$cadeb=$ventes['prix']+$venteslot['prix'];
		$qte12=intval($ventes12['qte']+$venteslot12['qte']);
		$ca12=$ventes12['prix']+$venteslot12['prix'];
		$qtemp=intval($ventesmp['qte']+$venteslotmp['qte']);
		$qtestock=intval($item->qte);

		$line.="<td class=\"$rowalt\" align=\"right\">".$qtedeb."</td>
		<td class=\"$rowalt\" align=\"right\">".number_format($cadeb,0,',','')."&euro;</td>
		<td class=\"$rowalt\" align=\"right\">".$qte12."</td>
		<td class=\"$rowalt\" align=\"right\">".number_format($ca12,0,',','')."&euro;</td>
		<td class=\"$rowalt\" align=\"right\">".$qtestock."</td>";
		
		$line.="</tr>";
		
		$tr[$$order]=$tr[$$order].$line;
      
    }

    $tdsite="";
    foreach($show_sites as $site_code=>$site) $tdsite.="<td align=\"center\">$site_code</td>";

    if(!$filter_langue) {
	echo "<h3>Articles</h3>";

	$nb_article=$DB_site2->query_first("select count(*) from article where catid=$parentid");
	print "<p>Afficher le site <select onchange=\"document.location='catalogue.php?catid=$catid&filter_site='+this.value\">
	<option></option>";
	foreach($sites as $site_code=>$site)
	    print "<option value=\"$site_code\"".($_GET['filter_site']==$site_code?" selected":"").">$site_code</option>";
	print "</select> &nbsp; <a href=\"catalogue.php?catid=$catid&all=1".(isset($_GET['filter_site'])?"&filter_site=".$_GET['filter_site']:"")."\">Afficher tous les articles + ceux supprimés et archivés (".$nb_article[0].")</a><br>";

	echo "<p><a href=\"ajout_produit.php?catid=$catid&new=true\">Créer un article <img src=\"images/ajouter.gif\" align=\"absmiddle\"></a></p>
	<table class=\"table_modules_boutiques catalogue\">
	<tr style=\"position:sticky;top:56px;background-color:white;z-index:9999;\">
	  <td></td>
	  <td align=\"center\"><a href=\"catalogue.php?catid=$catid&order=artcode&sens=$sensinv\">Référence</a>".($order=="artcode"?"<br>".$sensicon:"")."</td>
	  <td align=\"center\">Redirection</td>
	  <td colspan=\"2\"></td>
	  $tdsite
	  <td colspan=\"3\"></td>
	  <td align=\"center\"><a href=\"catalogue.php?catid=$catid&order=qtedeb&sens=$sensinv\">Qte vendue</a>".($order=="qtedeb"?"<br>".$sensicon:"")."</td>
	  <td align=\"center\"><a href=\"catalogue.php?catid=$catid&order=cadeb&sens=$sensinv\">CA HT</a>".($order=="cadeb"?"<br>".$sensicon:"")."</td>
	  <td align=\"center\"><a href=\"catalogue.php?catid=$catid&order=qte12&sens=$sensinv\">Qte vendue 1an</a>".($order=="qte12"?"<br>".$sensicon:"")."</td>
	  <td align=\"center\"><a href=\"catalogue.php?catid=$catid&order=ca12&sens=$sensinv\">CA HT 1an</a>".($order=="ca12"?"<br>".$sensicon:"")."</td>
	  <td align=\"center\"><a href=\"catalogue.php?catid=$catid&order=qtestock&sens=$sensinv\">Qte stock</a>".($order=="qtestock"?"<br>".$sensicon:"")."</td>
	</tr>";

	if($sens=="DESC") 
	    krsort($tr);
	else
	    ksort($tr);

	echo implode("",$tr);

	echo "</table>";
    }
    
    } 
else 
    {
    $nb_article=$DB_site2->query_first("select count(*) from article where catid=$catid");
    echo "<h3>Articles</h3>
	<p><a href=\"ajout_produit.php?catid=$catid\">Ajouter un article <img src=\"images/ajouter.gif\" align=\"absmiddle\"></a> &nbsp; <a href=\"catalogue.php?catid=$catid&all=1".(isset($_GET['filter_site'])?"&filter_site=".$_GET['filter_site']:"")."\">Afficher tous les articles + ceux supprimés et archivés (".$nb_article[0].")</a></p>
        <table class=\"table_modules_boutiques\">
            <tr><td align=\"center\"><b>Il n'y a aucun article dans ce rayon</b></td></tr>
        </table>";
    }


echo "<br><br><br><br>
<table class=\"table_modules_boutiques\">
    <tr>
        <td align=\"center\" valign=\"top\"><img src=\"images/ajouter.gif\"></td>
        <td align=\"left\" width=\"90%\"><strong>Ajouter</strong><br>Pour ajouter un nouveau rayon ou un nouvel article à votre boutique. Le rayon ou l'article sera créé dans l'arborescence à l'endroit où vous vous situez.</td>
    </tr>
    <tr>
        <td align=\"center\" valign=\"top\"><img src=\"images/editer.gif\"></td>
        <td align=\"left\" width=\"90%\"><strong>Editer</strong><br>Pour voir le détail de la fiche d'un rayon ou d'un article. Permet de modifier le rayon ou l'article sélectionné.</td>
    </tr>
    <tr>
        <td align=\"center\" valign=\"top\"><img src=\"images/deplacer.gif\"></td>
        <td align=\"left\" width=\"90%\"><strong>Déplacer</strong><br>Pour déplacer un rayon ou un article dans un autre rayon. Permet de sélectionner le rayon cible.</td>
    </tr>
    <tr>
        <td align=\"center\" valign=\"top\"><img src=\"images/copier.gif\"></td>
        <td align=\"left\" width=\"90%\"><strong>Copier</strong><br>Pour copier un rayon ou un article dans un autre rayon. Permet de sélectionner le rayon cible.</td>
    </tr>
    <tr>
        <td align=\"center\" valign=\"top\"><img src=\"images/supprimer.gif\"></td>
        <td align=\"left\" width=\"90%\"><strong>Supprimer</strong><br>Pour supprimer un article ou un rayon. Attention : cette action est irréversible. Si vous supprimez un rayon dans lequel il existe des articles et / ou des sous-rayons, ceux-cis seront également supprimés.</td>
    </tr>
    <tr>
        <td align=\"center\" valign=\"top\"><img src=\"images/voir.gif\"></td>
        <td align=\"left\" width=\"90%\"><strong>Voir</strong> (seulement pour les articles)<br>Pour voir la fiche de l'article dans la boutique (comme la verront vos clients).</td>
    </tr>
    <tr>
        <td align=\"center\" valign=\"top\"><img src=\"images/coeur.gif\"></td>
        <td align=\"left\" width=\"90%\"><strong>Coup de coeur</strong> (seulement pour les articles)<br>Pour gérer les coups de coeur sur les articles. En cliquant sur le coeur en face d'un article, vous activez ou désactivez le coup de coeur.</td>
    </tr>
    <tr>
        <td align=\"center\" valign=\"top\"><img src=\"images/promotion.gif\"></td>
        <td align=\"left\" width=\"90%\"><strong>Promotion</strong> (seulement pour les articles)<br>Pour gérer les promotions sur les articles. Si une promotion est en cours sur l'article sélectionné, vous visualisez cette promotion. S'il n'existe pas de promotion sur l'article, vous pouvez en créer une.</td>
    </tr>
</table>";


include "./footer.php";


function compter_produits($catid){
    global $DB_site2;
    $stringbre=0;
    $requete_nb_produits="select count(artid) from article where catid=$catid";
    $nb_produits=$DB_site2->query_first($requete_nb_produits);
    $stringbre=$nb_produits[0];

    $requete_ss_cat="select catid from categorie where parentid='$catid'";
    $sscats=$DB_site2->query($requete_ss_cat);
    $ssnombre=0;
    while ($sscat=$DB_site2->fetch_array($sscats)) {
        $ssnombre+=compter_produits($sscat['catid']);
    }
    $total=$stringbre+$ssnombre;
    return ($total);
}

function navigation($catidfunc , $isarticle=0){
    $separateur_navigation=" > ";
    global $DB_site2,$libelle,$navigationbit,$parentid;
    $requete_categorie="select catid , $libelle , parentid from categorie where catid='$catidfunc' ";
    $navigation_categorie=$DB_site2->query_first($requete_categorie);
    if ($parentid==$catidfunc and $isarticle==0) {
        $navigationbit=$navigation_categorie[$libelle];
    } else {
        $navigationbit="<a href=\"catalogue.php?catid=$navigation_categorie[catid]\">$navigation_categorie[$libelle]</a>" . $separateur_navigation . $navigationbit;
    }
    if ($navigation_categorie[parentid]!=0) {
        navigation($navigation_categorie[parentid]);
    }
}

function gen_position($menuid=null,$catid=null){
    global $DB_site2;
    
    $sql="SELECT * FROM categorie WHERE menu_appartenance=".intval($menuid);
    if(!is_null($catid)) $sql.=" AND parentid=".intval($catid);
    $sql.=" ORDER BY position,libelleFR";
    $req=$DB_site2->query($sql);
    $p=0;
    print "<table>";
    while($cat=$DB_site2->fetch_array($req)){
        $DB_site2->query("UPDATE categorie SET position=".$p." WHERE catid=".$cat['catid']);
        print "<tr><td>".$cat['catid']."</td><td>".$cat['libelleFR']."</td><td>".$cat['position']."</td><td>".$p."</td></tr>";
        $p++;
        print "<tr><td></td><td colspan=\"10\">";
        gen_position(null,$cat['catid']);
        print "</td></tr>";
    }
    print "</table>";
}

// gen_position(2);

?>
