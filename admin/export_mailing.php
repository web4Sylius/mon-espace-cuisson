<?php
require_once($_SERVER['DOCUMENT_ROOT']."/admin/admin_global.php");

//on veut faire appartaitre tous les types d'articles en checkbox
$typeArticle = $DB_site2->query("SELECT typearticleid, libelle FROM typearticle");
$i = 1;
$htmlType ="";
while( $type = $DB_site2->fetch_array($typeArticle)){
    //$htmlType .="<br><input class=\"type2\" type=\"checkbox\" name=\"type".$type['typearticleid']."\" value=\"".$type['typearticleid']."\" onclick=\"checkType();\">".$type['libelle'];
    $i++;
}

if(isset($_GET['clients'])){
    $params="&".$_GET['type']."=1";    
    if($_GET['pro']>-1) $params.="&pro=".$_GET['pro'];
    if($_GET['clients']>-1) $params.="&clients=".$_GET['clients'];
    if($_GET['origine']!="-1") $params.="&origine=".$_GET['origine'];
    if($_GET['civilite']!="-1") $params.="&civilite=".$_GET['civilite'];

    //On gere l'envoi des type d'articles
    $x = 0;
    $arts=array();
    for($y=1;$y<=$i;$y++){
        if(isset($_GET['type'.$y])){
            $arts[] =$_GET['type'.$y];
            $x++;
        }
    }
    $params .="&type=".serialize($arts);
    $params.="&x=".$x;

    if(isset($_GET['inscription'])){
        $from=date_create_from_format("j/n/Y",$_GET['subfrom']);
        $to=date_create_from_format("j/n/Y",$_GET['subto']);
        if($from!=false && $to!=false){
            $from->settime(0,0,0);
            $to->settime(23,59,59);
            $params.="&subfrom=".$from->gettimestamp()."&subto=".$to->gettimestamp();
        }
    }
    header("Location:export/clients.php?1".$params);
    exit;
}

include "./header.php";
include "./menu.php";



echo <<<HTML
<h1>Exports mailing & SMS</h1>

<form method="get">
    <select name="type">
        <option value="mail">Email</option>
        <option value="sms">SMS</option>
    </select>
    
    <br><br><input type="radio" name="pro" value="-1" checked> Tous
    <br><input type="radio" name="pro" value="0"> Particuliers
    <br><input type="radio" name="pro" value="1"> Pros
    
    <br><br><input type="radio" name="clients" value="-1" onclick="notClient();" checked> Tous
    <br><input type="radio" name="clients" value="0" onclick="notClient();"> Propects
    <br><input type="radio" name="clients" value="1" onclick="isClient();"> Clients
    
   <!-- <br><br><input type="radio" name="origine" value="-1" checked> Toutes
    <br><input type="radio" name="origine" value="mcavfr"> mcav
    <br><input type="radio" name="origine" value="mwccom"> mwc
    <br><input type="radio" name="origine" value="mwcuk"> mwc uk
    -->

    <br><br><input type="radio" name="civilite" value="-1" checked> Mixte
    <br><input type="radio" name="civilite" value="0"> Hommes
    <br><input type="radio" name="civilite" value="1"> Femmes

    <!--Ne marche pas
    <div id="typeArticle" style="display:none;"> 
    <br><br><span>Types d'articles :</span>
    <br><input class="type1" type="checkbox" name ="type" value="-1" onclick="checkType();" checked>Tous-->
HTML;

    echo $htmlType;


echo <<<HTML
    </div>-->
    <br><br><input type="checkbox" name="inscription" value="1" onclick="document.getElementById('datesinfos').style.display=(this.checked?'block':'none');"> Date d'inscription
    <div id="datesinfos" style="display:none;">
        entre le <input type="text" name="subfrom" id="subfrom" onclick="calendrier_show(this.id,this.value)" size="8" readonly> et <input type="text" name="subto" id="subto" onclick="calendrier_show(this.id,this.value)" size="8" readonly> inclu
    </div>
    <br><br><input type="submit" value="OK">
</form>
<script type="text/javascript">
function checkType(){
    if(jQuery(".type1").prop("checked") ){
        jQuery(".type2").prop("checked",false);
    }
}
function isClient(){
        jQuery("#typeArticle").show();
}
function notClient(){
        jQuery("#typeArticle").hide();
        jQuery("#typeArticle > input").prop("checked",false);
        jQuery(".type1").prop("checked",true);
}
</script>
HTML;

include "./footer.php";

?>
