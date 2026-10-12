<?php

require "admin_global.php";

include "./header.php";
include "./menu.php";

if(isset($_POST['action'])){
    if($_POST['action']=="addzone2"){
        if($_POST['prix']=="") $err="Il faut mettre un prix";
        if($_POST['from']=="") $err="Il faut mettre le poids minimum";
        if($_POST['to']=="") $err="Il faut mettre le poids maximum";
        if($_POST['zone']=="") $err="Il donner un nom à la zone";
        
        $tranche=$DB_site2->query_first("SELECT id FROM sls WHERE zone='".mysqli_real_escape_string($DB_site2->link_id,$_POST['zone'])."' AND ".intval($_POST['from']).">`from` AND ".intval($_POST['from'])."<`to`");
        if($tranche[0]!="") $err="Cette tranche se mélange avec une autre";

        if($err==""){
            $result=$DB_site2->query("INSERT INTO sls SET zone='".mysqli_real_escape_string($DB_site2->link_id,$_POST['zone'])."', `from`=".floatval($_POST['from']).", `to`=".floatval($_POST['to']).", prix=".floatval($_POST['prix']).", prixlv4=".floatval($_POST['prixlv4']));
            $_GET['zone']=$_POST['zone'];
            unset($_GET['action']);
            
        }else{
            $_GET['action']="addzone";
        }
    }
    
    if($_POST['action']=="update"){
        foreach($_POST['prix'] as $key=>$prix){
            $DB_site2->query("UPDATE sls SET prix=".floatval($prix).", prixlv4=".floatval($_POST['prixlv4'][$key])." WHERE id=".$key);
        }
        unset($_POST['action']);
    }

    if($_POST['action']=="upddep"){
        foreach($_POST as $key=>$zone){
            $zone = mysqli_real_escape_string($DB_site2->link_id,$zone);
            if(substr($key,0,3)=="dep" && $zone == "?"){
                $dep=substr($key,3);
                $exist = $DB_site2->query_first("SELECT slszone_id FROM slszones WHERE dep=".intval($dep));
                if(!is_null($exist)) $DB_site2->query("DELETE FROM slszones WHERE dep =".intval($dep)."");

            }elseif(substr($key,0,3)=="dep" && $zone!="?"){
                $dep=substr($key,3);
                $exist = $DB_site2->query_first("SELECT slszone_id FROM slszones WHERE dep=".intval($dep));
                if(!is_null($exist)){
                    $DB_site2->query("UPDATE slszones SET zone = '".$zone."' WHERE dep =".intval($dep)."");
                }else{
                    $lespays = $DB_site2->query_first("SELECT paysid FROM slszones WHERE zone='".$zone."' GROUP BY zone");
                    $DB_site2->query("INSERT INTO slszones SET dep =".intval($dep).", zone='".$zone."', paysid='".$lespays['paysid']."'");
                }
            }
        }
        $_GET['action']="departements";
    }

    if($_POST['action']=="pays"){
        foreach($_POST['zone'] as $zone=>$paysid){
            $nb=$DB_site2->query_first("SELECT COUNT(*) FROM slszones WHERE zone='".mysqli_real_escape_string($DB_site2->link_id,$zone)."'");
            if($nb[0]>0)
                $DB_site2->query("UPDATE slszones SET paysid='".$paysid."' WHERE zone='".mysqli_real_escape_string($DB_site2->link_id,$zone)."'");
            else
                $DB_site2->query("INSERT INTO slszones SET paysid='".$paysid."', zone='".mysqli_real_escape_string($DB_site2->link_id,$zone)."'");
        }
    }
}

echo "<script language=\"javascript\">
var curzone='';
function addpays(){
//     if(curzone=='') return;
    var name='zone['+curzone+']';
    var paysid=document.getElementsByName(name)[0].value.split(',');
    for(i=0;i<=paysid.length;i++) if(paysid[i]==document.getElementById('listpays').value) return;
    var virg='';
    if(document.getElementsByName(name)[0].value!='') virg=',';
    document.getElementsByName(name)[0].value+=virg+document.getElementById('listpays').value;
    document.getElementById('zone_'+curzone).innerHTML+='<p id=\"'+curzone+'_'+document.getElementById('listpays').value+'\">'+document.getElementById('listpays').options[document.getElementById('listpays').selectedIndex].innerHTML+' <span style=\"color:red; cursor:pointer;\" onclick=\"rmpays(\''+curzone+'\','+document.getElementById('listpays').value+')\">X</span></p>';
}
function rmpays(zone,paysid){
    var name='zone['+zone+']';
    var lspaysid=document.getElementsByName(name)[0].value.split(',');
    document.getElementsByName(name)[0].value='';
    var virg='';
    for(i=0;i<lspaysid.length;i++){
	if(lspaysid[i]!=paysid){
	  document.getElementsByName(name)[0].value+=virg+lspaysid[i];
	  virg=',';
	}
    }
    var elem=document.getElementById(zone+'_'+paysid);
    document.getElementById('zone_'+zone).removeChild(elem);
}
function selectzone(zone){
    if(curzone!='') document.getElementById('divz_'+curzone).style.borderColor='#888';
    curzone=zone;
    document.getElementById('divz_'+curzone).style.borderColor='#FF0000';
}
</script>";

echo "<h1>Prix de vente de l'installation</h1>" ;

if(!isset($_GET['action'])){
    print "<p><a href=\"sls.php?action=addzone\">Ajouter une zone</a> &nbsp; <a href=\"sls.php?action=departements\">Attribuer les départements</a> &nbsp; <a href=\"sls.php?action=pays\">Attribuer les pays</a></p>";
    
    $lastzone="";
    print "<form method=\"post\">
    <input type=\"hidden\" name=\"action\" value=\"update\">
    <table width=\"200\">
    <tr>
        <td align=\"center\">De</td>
        <td align=\"center\">à</td>
        <td align=\"center\">Prix LV2 TTC</td>
        <td align=\"center\">Prix LV4 TTC</td>
    </tr>";
    
    $req=$DB_site2->query("SELECT zone,paysid FROM slszones WHERE paysid<>'' GROUP BY zone");
    while($z=$DB_site2->fetch_array($req)){
        $req2=$DB_site2->query("SELECT diminutif,libelle FROM pays WHERE paysid IN(".$z['paysid'].")");
        while($pays=$DB_site2->fetch_array($req2)){
            $slspays[$z['zone']].="<img src=\"images/drapeaux/".$pays['diminutif'].".gif\" title=\"".$pays['libelle']."\" width=\"20\" align=\"absmiddle\"> ";
        }
    }
      
    $sql="SELECT * FROM sls";
    if(isset($_GET['zone'])) $sql.=" WHERE zone='".$_GET['zone']."'";
    $sql.=" ORDER BY zone,`from`";
    $requete=$DB_site2->query($sql);
    while($sls=$DB_site2->fetch_array($requete)){
        if($sls['zone']!=$lastzone){
            if($lastzone!="") print "<tr><td colspan=\"3\" align=\"center\"><a href=\"sls.php?action=addzone&zone=".$lastzone."\">Ajouter une tranche</a></td></tr>";
            print "<tr style=\"background-color:#cccccc;font-weight:bold;\">
                <td colspan=\"2\" align=\"center\">".$sls['zone']."</td>
                <td colspan=\"2\" align=\"right\">".$slspays[$sls['zone']]."</td>
            </tr>";
            $lastzone=$sls['zone'];
        }
        print "<tr>
            <td align=\"right\">".$sls['from']."</td>
            <td align=\"right\">".$sls['to']."</td>
            <td align=\"right\"><input type=\"text\" name=\"prix[".$sls['id']."]\" value=\"".$sls['prix']."\" size=\"4\"></td>
            <td align=\"right\"><input type=\"text\" name=\"prixlv4[".$sls['id']."]\" value=\"".$sls['prixlv4']."\" size=\"4\"></td>
        </tr>";
    }
    
    if($lastzone!="") print "<tr>
        <td colspan=\"4\" align=\"center\"><a href=\"sls.php?action=addzone&zone=".$lastzone."\">Ajouter une tranche</a></td>
    </tr>";
    print "<tr>
        <td colspan=\"4\" align=\"center\"><input type=\"submit\" value=\"Mettre à jour\"></td>
    </tr>
    </table>";
}else{
    switch($_GET['action']){
    case "addzone":
    print "<form method=\"post\">
    <input type=\"hidden\" name=\"action\" value=\"addzone2\">$err
    <p>Zone : <input type=\"text\" name=\"zone\" value=\"".$_GET['zone']."\" size=\"6\" readonly></p>
    <p>Poids : de <input type=\"text\" name=\"from\" value=\"0\" size=\"6\"> g à <input type=\"text\" name=\"to\" size=\"6\"> g</p>
    <p>Prix LV2 : <input type=\"text\" name=\"prix\" size=\"6\"> &euro; TTC</p>
    <p>Prix LV4 : <input type=\"text\" name=\"prixlv4\" size=\"6\"> &euro; TTC</p>
    <p><input type=\"submit\" value=\"Ajouter\"> <input type=\"button\" value=\"Annuler\" onclick=\"document.location='sls.php';\"></p>
    </form>";
    break;

    case "departements":
    $assoc=array();
    $zones=array("?");
    $requete=$DB_site2->query("SELECT * FROM slszones");
    while($dep=$DB_site2->fetch_array($requete)) $assoc[$dep['dep']]=$dep['zone'];
    $requete=$DB_site2->query("SELECT * FROM sls GROUP BY zone");
    while($z=$DB_site2->fetch_array($requete)) $zones[]=$z['zone'];
    
    print "<form method=\"post\">
    <input type=\"hidden\" name=\"action\" value=\"upddep\">
    <div style=\"float:left;width:100px;\">";
    
    $l=0;
    for($i=1;$i<=98;$i++){
        $l++;
        if($l==20){
            $l=0;
            print "</div><div style=\"float:left;width:100px;\">";
        }
        $opt="";
        foreach($zones as $zone) $opt.="<option value=\"$zone\"".($zone==$assoc[$i]?" selected":"").">$zone</option>";
        print "<p>".$i." <select name=\"dep$i\">$opt</select></p>";
    }
    print "</div>
    <div style=\"clear:both;text-align:center;\"><input type=\"submit\" value=\"Mettre à jour\"></div></form>";
    break;

    case "pays":
    print "<form method=\"post\">
    <input type=\"hidden\" name=\"action\" value=\"pays\">
    <div style=\"width:40%;float:left;\">";
    $req=$DB_site2->query("SELECT zone,paysid FROM sls LEFT JOIN slszones USING(zone) GROUP BY zone");
    while($zone=$DB_site2->fetch_array($req)){
        print "<div id=\"divz_".$zone['zone']."\" onclick=\"selectzone('".$zone['zone']."')\" style=\"border:solid thin #888;\">
    <div style=\"background-color:#CCC;\">".$zone['zone']."</div>
    <input type=\"hidden\" name=\"zone[".$zone['zone']."]\" value=\"".$zone['paysid']."\">
    <div id=\"zone_".$zone['zone']."\">";
    if($zone['paysid']!=null){
        $req2=$DB_site2->query("SELECT paysid,diminutif,libelle FROM pays WHERE paysid IN(".$zone['paysid'].")");
        while($pays=$DB_site2->fetch_array($req2)){
            print "<p id=\"".$zone['zone']."_".$pays['paysid']."\">".$pays['libelle']." <span style=\"color:red; cursor:pointer;\" onclick=\"rmpays('".$zone['zone']."',".$pays['paysid'].")\">X</span></p> ";
        }
    }
    print "</div>
    </div><br>";
    }
    print "</div>
    <div style=\"width:40%;float:left;margin-left:10px;\">
    Ajouter un pays
    <select id=\"listpays\">";
    $req=$DB_site2->query("SELECT * FROM pays WHERE 1 ORDER BY libelle");
    while($pays=$DB_site2->fetch_array($req)) print "<option value=\"".$pays['paysid']."\">".$pays['libelle']."</option>";
    print "</select>
    <input type=\"button\" value=\"&lt;&lt;\" onclick=\"addpays()\">
    </div>";
    print "<div style=\"clear:both;text-align:center;\"><input type=\"submit\" value=\"Mettre à jour\"> <input type=\"button\" value=\"Annuler\" onclick=\"document.location='sls.php';\"></div></form>";
    break;
    }
}



include "./footer.php";
?>
