<?php

require "admin_global.php";
include "./header.php";
include "./menu.php";
include "./fonctions.php";
include "../class/categorie.class.php";

echo "<h1>Déplacer un rayon</h1>" ;

function arborescence($menuid,$parentid){
	global $DB_site2,$select,$niveau,$libelle,$catid,$libellecat;

	$requete_arbo="SELECT $libelle,catid FROM categorie WHERE 1".(!is_null($menuid)?" AND menu_appartenance=".$menuid:"").(!is_null($parentid)?" AND parentid='$parentid'":"")." AND catid!=".$catid." ORDER BY position ";
	
	$arbos=$DB_site2->query($requete_arbo);
	while ($arbo=$DB_site2->fetch_array($arbos)){
                $sscat=$DB_site2->query_first("SELECT COUNT(*) FROM categorie WHERE parentid=".$arbo['catid']);
                if($menuid==0 && $parentid==0){
                    $sscat=$DB_site2->query_first("SELECT COUNT(*) FROM categorie WHERE parentid='$arbo[catid]' AND menu_appartenance>0");
                    if($sscat[0]>0) continue;
                }
                $select.="<option value=\"0|$arbo[catid]\" data-subcat=\"".$sscat[0]."\">".$arbo[$libelle].($sscat[0]>0?" &gt;":"")."</option>";
	}

	return ($select);
}

function invisible(){
        global $DB_site2,$select;
        
        
}


$catid=$_GET['catid'];
if(isset($_POST['catid'])) $catid=$_POST['catid'];
if ($catid=="" || !isset($catid))
{
	echo "ERREU !!!";
	include "./footer.php";
	exit;
}

$action=isset($_POST['action']) ? $_POST['action'] : null;

//*******************************************************************
//****************AFFICHAGE BARRE NAVIGATION*************************
//*******************************************************************


if(is_null($action)){
    $menuid=isset($_GET['menuid']) ? intval($_GET['menuid']) : 0;
    $newcatid=isset($_GET['newcatid']) ? intval($_GET['newcatid']) : null;

    $categorie=new categorie($DB_site2,$_GET['catid']);

    if($menuid==0 && is_null($newcatid)){
            $sqlmenu="SELECT * FROM menus ORDER BY position";
            $rmenu=$DB_site2->query($sqlmenu);
            while($menu=$DB_site2->fetch_array($rmenu)){
                $select.="<option value=\"".$menu['idmenu']."|\" data-subcat=\"1\">".$menu['libelleFR']." &gt;</option>";
            }

            $invcat=0;
            $req=$DB_site2->query("SELECT * FROM categorie WHERE parentid=0 AND menu_appartenance=0 ORDER BY libelle".$langue);
            if($DB_site2->num_rows($req)>0){
                while($cat=$DB_site2->fetch_array($req)){
                    $sscats=$DB_site2->query_first("SELECT COUNT(*) FROM categorie WHERE parentid='$cat[catid]' AND menu_appartenance>0");
                    if($sscat[0]==0) $invcat++;
                }
            }
            $select.="<option value=\"0|0\" data-subcat=\"".$invcat."\" style=\"color:red;\">Invisible sur le site".($invcat>0?" &gt;":"")."</option>";
            
            $dpltxt="le menu";
    }else{
            $dpltxt=" la catégorie";
            $newcat=new categorie($DB_site2,$newcatid);
            $select.="<option value=\"".intval($newcat->menu_appartenance)."|".$newcat->parentid."\" data-subcat=\"1\">&lt;</option>";
            arborescence($menuid,$newcatid);
    }


    echo "<form action=\"deplacer_categorie.php\" method=\"post\">
            Déplacer le rayon '$categorie->libelle' dans $dpltxt :<bR>
            <br><select name=\"catlist\" size=\"30\" ondblclick=\"navigate()\" onclick=\"document.getElementById('btnsubmit').disabled=false;\">
            $select
            </select><br><br>
            <input type=\"hidden\" name=\"catid\" value=\"$catid\">
            <input type=\"hidden\" name=\"action\" value=\"deplacer\">
            <input type=\"submit\" id=\"btnsubmit\" value=\"Déplacer\" disabled>
    </form>
    <br><br><a href=catalogue.php>Retour</a>";

}

if ($action == "deplacer"){
        list($menuid,$newcatid)=explode("|",$_POST['catlist']);
        $newcatid=intval($newcatid);
        
	if ($newcatid != $catid){
                $DB_site2->query(" UPDATE categorie SET parentid='$newcatid', menu_appartenance='$menuid' WHERE catid='$catid' ");
                echo "Ce rayon a bien été déplacé...<br><br>
                <a href=catalogue.php>Retour</a>";
        }else{
                echo "<p>Aucun déplacement<br><a href=\"javascript:history.back();\">Retour</a></p>";
        }
}

print <<<JAVASCRIPT
        <script type="text/javascript">
            function navigate(){
                var catid=document.getElementsByName('catid')[0].value;
                var catlist=document.getElementsByName('catlist')[0];
                var d=catlist.value.split('|');
                var opt=catlist.options[catlist.selectedIndex];
                if(opt.dataset.subcat>0){
                    var url="deplacer_categorie.php?catid="+catid+"&menuid="+d[0];
                    if(d[1]!="") url+="&newcatid="+d[1];
                    document.location=url;
                }
            }
        </script>
JAVASCRIPT;

include "./footer.php";
?>