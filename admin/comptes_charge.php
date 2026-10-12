<?php

require "admin_global.php";

if(isset($_POST['fourn'])){
    $ff=$DB_site2->query_first("SELECT * FROM fournisseur_frais WHERE fournisseur='".mysqli_real_escape_string($DB_site2->link_id,$_POST['fourn'])."'");
    if($ff!=""){
        $return=$DB_site2->query("UPDATE fournisseur_frais SET compte='".mysqli_real_escape_string($DB_site2->link_id,$_POST['code'])."' WHERE fournisseur='".mysqli_real_escape_string($DB_site2->link_id,$_POST['fourn'])."'");
    }elseif($_POST['type']!=""){
        if($_POST['code']!=""){
            $code=substr($_POST['code'],0,6);
            $cc=$DB_site2->query_first("SELECT * FROM code_comptable WHERE fournisseur='".mysqli_real_escape_string($DB_site2->link_id,$_POST['fourn'])."' AND type='".mysqli_real_escape_string($DB_site2->link_id,$_POST['type'])."'");
            if($cc!=""){
                $return=$DB_site2->query("UPDATE code_comptable SET code='".mysqli_real_escape_string($DB_site2->link_id,$code)."' WHERE fournisseur='".mysqli_real_escape_string($DB_site2->link_id,$_POST['fourn'])."' AND type='".mysqli_real_escape_string($DB_site2->link_id,$_POST['type'])."'");
            }else{
                $info=$DB_site2->query_first("SELECT info FROM code_comptable WHERE type='".mysqli_real_escape_string($DB_site2->link_id,$_POST['type'])."'");
                $return=$DB_site2->query("INSERT INTO code_comptable SET code='".mysqli_real_escape_string($DB_site2->link_id,$code)."', fournisseur='".mysqli_real_escape_string($DB_site2->link_id,$_POST['fourn'])."', type='".mysqli_real_escape_string($DB_site2->link_id,$_POST['type'])."', info='".mysqli_real_escape_string($DB_site2->link_id,$info['info'])."'");
            }
        }else{
            $return=$DB_site2->query("DELETE FROM code_comptable WHERE fournisseur='".mysqli_real_escape_string($DB_site2->link_id,$_POST['fourn'])."' AND type='".mysqli_real_escape_string($DB_site2->link_id,$_POST['type'])."'");
        }
    }
    print intval($return);
    exit;
}

include "./header.php";
include "./menu.php";

$order=isset($_GET['order']) ? $_GET['order'] : "fournisseur";

$tr=array();

$sql="SELECT * FROM code_comptable WHERE 1";
$sql.=" ORDER BY fournisseur";

$req=$DB_site2->query($sql);
while($cc=$DB_site2->fetch_array($req)){
    if(!isset($tr[$cc[$order]])) $tr[$cc[$order]]="";
    
    $codeCEE = '';
    if($cc['fournisseur']!=""){
        $sql = "SELECT p.* FROM pays p INNER JOIN fournisseur USING(paysid) WHERE societe = '".mysqli_real_escape_string($DB_site2->link_id,$fournisseur)."' AND type = 'facture'";
        $aPays = $DB_site2->query_first($sql);
        if($aPays['tvaintracom'] == 0) {
            if($aPays['cee'] == 1){
                $codeCEE = "0000"; // france
            } else {
                $codeCEE = "0999"; // hors cee
            }
        } else {
            $codeCEE = "0099"; // cee sans tva
        }
    }

    $tr[$cc[$order]].=<<<HTML
        <tr>
            <td>$cc[fournisseur]</td>
            <td>$cc[info]</td>
            <td><input type="text" value="$cc[code]$codeCEE" onfocus="this.style.border='solid 1px black';" onblur="this.style.border='none'; save(this);" data-type="$cc[type]" data-fourn="$cc[fournisseur]"></td>
        </tr>
HTML;
}

$sql="SELECT * FROM fournisseur_frais WHERE compte!=''";
$sql." ORDER BY fournisseur";

$req=$DB_site2->query($sql);
while($cc=$DB_site2->fetch_array($req)){
    $cc["code"]=$cc["compte"];
    if(!isset($tr[$cc[$order]])) $tr[$cc[$order]]="";

    $tr[$cc[$order]].=<<<HTML
        <tr>
            <td>$cc[fournisseur]</td>
            <td></td>
            <td><input type="text" value="$cc[compte]" onfocus="this.style.border='solid 1px black';" onblur="this.style.border='none'; save(this);" data-type="" data-fourn="$cc[fournisseur]"></td>
        </tr>
HTML;
}

ksort($tr);
$trhtml=implode("",$tr);

$orderfourn=$order=="fournisseur" ? " ▼" : "";
$orderinfo=$order=="info" ? " ▼" : "";
$ordercode=$order=="code" ? " ▼" : "";

print <<<HTML
<h1>Comptes de charge</h1>
<p>Les codes par défaut (sans fournisseur attribué) doivent avoir 6 chiffres, les 4 derniers seront ajoutés automatiquement en fonction du pays)</p>
<table>
    <thead>
        <tr>
            <td><a href="comptes_charge.php?order=fournisseur">Fournisseur</a>$orderfourn</td>
            <td><a href="comptes_charge.php?order=info">Type</a>$orderinfo</td>
            <td><a href="comptes_charge.php?order=code">Code</a>$ordercode</td>
        </tr>
    </thead>
    $trhtml
</table>
<style>
table { border-collapse:collapse; border:solid 1px black; }
table tr:nth-child(even) { background:#CCC; }
table thead tr { background:yellow; color:black; text-align:center; }
table td { padding:4px; border-left:solid 1px black; }
table input {
	background: none;
	border: none;
	width: 100px;
	font-size: 12px;
	margin-right: 0px;
}
table .loader { display:block; margin:auto; width:20px; }
</style>
<script type="text/javascript">
    function save(input){
        var post=new Array();
        post['type']=input.dataset.type;
        post['code']=input.value;
        post['fourn']=input.dataset.fourn;
        input.style.display="none";
        var wait=document.createElement("img");
        wait.src="../images/templates/wait.gif";
        wait.className="loader";
        input.parentNode.appendChild(wait);
        ajaxrequest("comptes_charge.php",function(httprequest){
            input.style.display="inline";
            input.parentNode.removeChild(wait);
        },post);
    }
</script>
HTML;

include "./footer.php";
?>
