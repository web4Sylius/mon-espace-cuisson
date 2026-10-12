<?php

require_once($_SERVER['DOCUMENT_ROOT']."/admin/admin_global.php");


include_once(BACK_PATH."/header.php");
include_once(BACK_PATH."/menu.php");

if(isset($_POST['id'])){
    $sql="societe='".mysqli_real_escape_string($DB_site2->link_id,$_POST['fournisseur'])."'";
    foreach($langues as $langue){
        $sql.=",`infos".$langue."`='".mysqli_real_escape_string($DB_site2->link_id,$_POST['infos'.$langue])."', `contact".$langue."`='".mysqli_real_escape_string($DB_site2->link_id,$_POST['contact'.$langue])."'";
    }
    if($_POST['id']==="")
        $sql="INSERT INTO fournisseur_livraison SET ".$sql;
    else
        $sql="UPDATE fournisseur_livraison SET ".$sql." WHERE id=".intval($_POST['id']);
    $DB_site2->query($sql);
}

?>

<h1>Infos livraison transporteurs</h1>

<?php

if(!isset($_GET['fournisseur'])){
    $table="<table>";

    $sql="SELECT fournisseur.societe FROM `fournisseur` inner join fournisseur_condition using(societe) WHERE `type` IN ('trspt','sls') and activite='1' group by societe";

    $req=$DB_site2->query($sql);
    while($f=$DB_site2->fetch_array($req)){
        $req2=$DB_site2->query("SELECT * FROM fournisseur_livraison WHERE societe='".mysqli_real_escape_string($DB_site2->link_id,$f['societe'])."'");
        if($DB_site2->num_rows($req2)>0){
            while($infos_livr=$DB_site2->fetch_array($req2)){
                if($infos_livr['trspttype']!="") $trspttype=" (".$infos_livr['trspttype'].")";
                
                $table.="<tr class=\"flist\">
                    <td style=\"color:green\">".$f['societe'].$trspttype."</td>
                    <td><a href=\"fournisseur_livraison.php?fournisseur=".$f['societe']."&id=".$infos_livr['id']."\"><img src=\"images/icon/edit.png\"></a></td>
                </tr>";
            }
        }else{
            $table.="<tr class=\"flist\">
                    <td style=\"color:red;\">".$f['societe'].$trspttype."</td>
                    <td><a href=\"fournisseur_livraison.php?fournisseur=".$f['societe']."\"><img src=\"images/icon/edit.png\"></a></td>
                </tr>";
        }
    }

    $table.="</table>";

    print $table;
}else{
    $infos_livr=$DB_site2->query_first("SELECT * FROM fournisseur_livraison WHERE societe='".mysqli_real_escape_string($DB_site2->link_id,$_GET['fournisseur'])."'".(isset($_GET['id'])?" AND id=".$_GET['id']:""));

    $fields="";
    foreach($langues as $langue){
        $infos=$infos_livr["infos".$langue];
        $contact=$infos_livr["contact".$langue];
        $flag=$langue=="EN"?"GB":$langue;
        
        $fields.=<<<HTML
            <p><img src="images/drapeaux/$flag.gif" width="20"> Infos livraison $langue :</p>
            <textarea name="infos$langue" class="ckeditor">$infos</textarea>
            <p><img src="images/drapeaux/$flag.gif" width="20"> Contact $langue :</p>
            <textarea name="contact$langue" class="ckeditor">$contact</textarea>
HTML;
    }
    
    print <<<HTML
        <h2>$_GET[fournisseur] $infos_livr[trspttype]</h2>
        <form method="post" action="fournisseur_livraison.php">
            <input type="hidden" name="id" value="$infos_livr[id]">
            <input type="hidden" name="fournisseur" value="$_GET[fournisseur]">
            $fields
            <input type="button" value="Annuler" onclick="document.location='fournisseur_livraison.php'"> <input type="submit" value="Valider">
        </form>
HTML;
}

include "./footer.php";

?>
