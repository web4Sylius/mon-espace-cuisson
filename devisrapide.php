<?php

include("global.php");

$artid=intval($_GET['artid']);
$gclid=$_GET['gclid'];

$optpays="<option value=\"\">".$template->translate("/menu/choisir","F")." ...</option>";
$requete=$DB_site->query("SELECT p.libelle,p.paysid FROM pays p INNER JOIN fraisport USING(paysid) WHERE sites='".$template->site_code()."' GROUP BY paysid ORDER BY libelle");
while($p=$DB_site->fetch_array($requete)){
    if($user_info['paysid']==$p['paysid']){
        $template->parts["pays"] = $p['libelle'];
        $template->parts["paysid"] = $p['paysid'];
    }
    $optpays.="<option value=\"$p[paysid]\"".($user_info['paysid']==$p['paysid']?" selected":"").">".$p[libelle]."</option>";
}

$js=<<<JS
<script language="javascript">
function checkMail(mail){
    var reg = new RegExp('^[a-z0-9]+([_|\.|-]{1}[a-z0-9]+)*@[a-z0-9]+([_|\.|-]{1}[a-z0-9]+)*[\.]{1}[a-z]{2,6}$', 'i');
    return(reg.test(mail));
}
function showlivr(){
    if(!document.getElementById('livrid').checked){
      document.getElementById('livrtbl').style.display='none';
      document.getElementById('mentions').style.display='none';
      if(document.documentElement.clientWidth>=640)
	  document.getElementById('img').style.display='';
    }else{
      document.getElementById('livrtbl').style.display='block';
      document.getElementById('mentions').style.display='block';
      /*if(document.documentElement.clientWidth>=640)
	  document.getElementById('img').style.display='none';*/
    }
}

function sendit(){
    err='';
    /*var cgv = document.getElementById('devis_immediat_cgv').getElementsByTagName('input')[0].checked;
    if(!cgv) err+='{$template->translate("/client/erreur_cgv","F")}.';*/
    if(document.getElementById('nom').value=='') err+=' {$template->translate("/client/erreur_nom","F")}.';
    if(document.getElementById('prenom').value=='') err+=' {$template->translate("/client/erreur_prenom","F")}.';
    if(document.getElementById('mail').value=='') err+=' {$template->translate("/client/indiquer_mail_valide","F")}.';
	else if(!checkMail(document.getElementById('mail').value)) err+=' {$template->translate("/client/indiquer_mail_valide","F")}.';
    if(document.getElementById('telephone').value+document.getElementById('portable').value=='') err+=' {$template->translate("/client/erreur_telephone","F")}.';
    if(document.getElementById('cp').value=='') err+=' {$template->translate("/client/erreur_codepostal","F")}.';
    if(document.getElementById('pays').value=='') err+=' {$template->translate("/client/erreur_pays","F")}.';
    if(document.getElementById('livrid').checked && document.getElementById('nom_livr').value=='') err+=' {$template->translate("/client/erreur_nom","F")}.';
    if(document.getElementById('livrid').checked && document.getElementById('prenom_livr').value=='') err+=' {$template->translate("/client/erreur_prenom","F")}.';
    if(document.getElementById('livrid').checked && document.getElementById('adresse1_livr').value=='') err+=' {$template->translate("/client/erreur_adresse","F")}.';
    if(document.getElementById('livrid').checked && document.getElementById('ville_livr').value=='') err+=' {$template->translate("/client/erreur_ville","F")}.';
    if(document.getElementById('livrid').checked && document.getElementById('payslivr').value=='') err+=' {$template->translate("/client/erreur_pays","F")}.';
    if(document.getElementById('livrid').checked && document.getElementById('cp_livr').value=='') err+=' {$template->translate("/client/erreur_codepostal","F")}.';
    /*if(!document.getElementById('conseils0').checked && !document.getElementById('conseils1').checked) err+=' {$template->translate("/client/erreur_conseils","F")}';*/
    if(err!=''){
		alert(err);
		return;
    }

    document.getElementById('sendbtn').style.display='none';
    document.getElementById('waiticon').style.display='block';
    params='societe='+document.getElementById('societe').value;
	params+='&nom='+document.getElementById('nom').value;
    params+='&civ='+document.getElementById('civ').value;
    params+='&prenom='+document.getElementById('prenom').value;
	params+='&adresse1='+document.getElementById('adresse1').value;
    params+='&cp='+document.getElementById('cp').value;
    params+='&ville='+document.getElementById('ville').value;
	params+='&mail='+document.getElementById('mail').value;
    params+='&telephone='+document.getElementById('telephone').value;
	params+='&portable='+document.getElementById('portable').value;
	params+='&pays='+document.getElementById('pays').value;
    params+='&payslivr='+document.getElementById('payslivr').value;
	params+='&livrid='+document.getElementById('livrid').checked;
    params+='&devisartid='+document.getElementById('devisartid').value;
    params+='&societe_livr='+document.getElementById('societe_livr').value;
	params+='&nom_livr='+document.getElementById('nom_livr').value;
    params+='&prenom_livr='+document.getElementById('prenom_livr').value;
	params+='&adresse1_livr='+document.getElementById('adresse1_livr').value;
    params+='&cp_livr='+document.getElementById('cp_livr').value;
    params+='&ville_livr='+document.getElementById('ville_livr').value;
	params+='&telephone_livr='+document.getElementById('telephone_livr').value;

    ajaxrequest('devisrapide_ajax.php?'+params,cb);
    
}

function cb(httprequest){
    document.getElementById('main').innerHTML=httprequest.responseText;
}
</script>
JS;

//Récupération des données spécifiques au type d'article
$sql = "SELECT bdd_table FROM typearticle";
$sql .= " INNER JOIN article a USING(typearticleid)";
$sql .=" WHERE a.artid= '$artid'";
$table_type = $DB_site->query_first($sql);

if($table_type[0]==""){
	echo "erreur inconnue";
	exit;
}

$article=$DB_site->query_first("SELECT a.libelle".$template->langue.",a.artcode,a.gamme".$template->langue.",a.artid,a.image,m.libelle,m.marqueid, a.simu FROM article a INNER JOIN ".$table_type[0]." as spec ON a.artid=spec.artid INNER JOIN article_marque am ON (am.artid=a.artid) INNER JOIN marque m ON (m.marqueid=am.marqueid) WHERE a.artid='$artid'");

//pour les articles simulateur on cree la photo en fonction des options choisies
if($article['simu'] == 'oui'){

    /*CREATION DE LA PHOTO WINEBAR SI ARTICLE SIMULATEUR*/
    $dimension = array(
        1 => array (800 , 800),
        2 => array (800 , 800), 
        3 => array (800 , 800),
        4 => array (800 , 800)    
    ) ;
    $position = array(
        1 => array (0 , 0),
        2 => array (0 , 0), 
        3 => array (0 , 0),
        4 => array (0 , 0)
    );
    $cal = 0;
    foreach ($_COOKIE as $key => $value) {
        $calque = strpos($key, "calque");
        if (is_numeric($calque)) $cal ++ ;
   }

    for($y=1;$y<=$cal;$y++){

        if($y == 1){
            $dest = imagecreatefromjpeg($rootpath."configurations/".$host."/images/simulateur/fond.jpg");
            
        }else{
            $dest = imagecreatefrompng($rootpath."simulateur/devis/".$artid."_".$_COOKIE['s'].".png");
        }
        $resource = imagecreatefrompng($rootpath."configurations/".$host."/images/simulateur/".$artid."_".$_COOKIE['calque'.$y].".png");

        //imagecopyresampled($dest, $resource, $position[$y][0], $position[$y][1], 0, 0, $dimension[$y][0], $dimension[$y][1], imagesx($resource), imagesy($resource));
        imagecopyresampled($dest, $resource, $position[$y][0], $position[$y][1], 0, 0, $dimension[$y][0], $dimension[$y][1], imagesx($resource), imagesy($resource));
        imagepng($dest,$rootpath."simulateur/devis/".$artid."_".$_COOKIE['s'].".png");
        imagedestroy($dest);
    }
    resizeImage($rootpath."simulateur/devis/".$artid."_".$_COOKIE['s'].".png",280,320,$rootpath."simulateur/devis/".$artid."_".$_COOKIE['s'].".png");
    $dst_img = imagecreatefrompng($rootpath."simulateur/devis/".$artid."_".$_COOKIE['s'].".png");
    $src_img = imagecreatefromgif($rootpath."images/templates/logo_wm.gif");
    imagecopymerge($dst_img, $src_img, 40, 100, 0, 0, 56, 56, 50);
    imagepng($dst_img,$rootpath."simulateur/devis/".$artid."_".$_COOKIE['s'].".png");
    /* * * * * FIN CREATION PHOTO WINEBAR * * * * * * * */
    $articlePhoto = $rootpath."simulateur/devis/".$artid."_".$_COOKIE['s'].".png?".time();
    $articlePhoto2 = "";
}else{
    $articlePhoto = article_image($article['artid'],280,320);
    $articlePhoto2 = article_image($article['artid'],180,220);
}


$template->parts["libelle"]=$article[0];
$template->parts["marque"]=$article['libelle'];
$template->parts["ref"]=$article['artcode'];
$template->parts["img"]=$articlePhoto;
$template->parts["imglivr"]=$articlePhoto2;
$template->parts["artid"]=$article[artid];
$template->parts["url"]=$template->mkurl("article",$artid,$article);
$template->parts["marqueid"]=$article[marqueid];
$template->parts['civ']=intval($user_info['civilite']);
if($user_info['civilite']==0) $template->parts["civ0"]="checked";
if($user_info['civilite']==1) $template->parts["civ1"]="checked";
$template->parts['nom']=$user_info['nom'];
$template->parts['prenom']=$user_info['prenom'];
$template->parts['raison_sociale']=$user_info['raisonsociale'];
if($template->parts['raison_sociale'] != '') $template->parts['raison_sociale'] .="<br>";
$template->parts['mail']=$user_info['mail'];
$template->parts['adresse1']=$user_info['adresse'];
$template->parts['codepostal']=$user_info['codepostal'];
$template->parts['ville']=$user_info['ville'];
$template->parts['telephone']=$user_info['telephone'];
$template->parts['portable']=$user_info['portable'];
$template->parts["optpays"]=$optpays;
if($user_info['raisonsociale'] == '') $template->parts['classPro'] = "masque";
if($template->mobile){
    $template->parts["js"]=$js;
    $template->evaltemplate("DevisRapide","main");
    $template->out();
}else{
    print <<<HTML
    <head>$js</head>
    <body>
HTML;

    if($user_info['userid'] == ''){ // on vérifie si l'utilisateur est connecté pour lui afficher le bon template.
        $template->parts['gclid2'] = $gclid;
        $template->parts['raisons'] = $_GET['raison'];
        $template->evaltemplate("DevisRapide2","html");
    }else{
        $template->evaltemplate("DevisRapide","html");
    }
    
    print stripslashes($template->parts["html"]);
    print "</body>";
}

function resizeImage($srcFile, $dstWidth = 120, $dstHeight = 90,$dstFile = null, $jpegQuality = 100){
    global $msg2;
    if (!file_exists($srcFile)) {
        $msg2="$srcFile doesn't exist! ";
        return false;        
    }
    if (!$dstFile) {
        $dstFile = 'tn_' . basename($srcFile);
    }
    if (!is_writable($dstDir = dirname($dstFile))) {
        $msg2="$dstDir is not a writeable directory! ";
        return false;
    }

    list($srcWidth, $srcHeight, $type, ) = getimagesize($srcFile);

    switch ($type) {
    case 1 :
        $srcHandle = imagecreatefromgif($srcFile);
        break;
    case 2 :
        $srcHandle = imagecreatefromjpeg($srcFile);
        break;
    case 3 :
        $srcHandle = imagecreatefrompng($srcFile);
        break;
    default :
        $msg2='File Type Not Supported! ';
        return false;
    }
    if (!$srcHandle) {
        $msg2='Could not execute imagecreatefrom() function! ';
        return false;
    }

    // Source is wider than the Destination
    $width = $dstWidth;
    $xOffset = 0;

    $height = round(($width / $srcWidth) * $srcHeight);
    $yOffset = 0;

    $dstHandle = imagecreatetruecolor($width, $height);
    imagecolorallocate($dstHandle, 255, 255, 255);

    // bool imagecopyresampled ( resource dst_image, resource src_image, int dst_x, int dst_y, int src_x, int src_y, int dst_w, int dst_h, int src_w, int src_h )
    if (!imagecopyresampled($dstHandle, $srcHandle, $xOffset, $yOffset, 0, 0, $width, $height, $srcWidth, $srcHeight)) {
        $msg2='Could not execute imagecopyresampled() function! ';
        return false;
    }
    imagedestroy($srcHandle);

    switch ($type) {
    case 1 :
        imagegif($dstHandle, $dstFile);
        break;
    case 2 :
        imagejpeg($dstHandle, $dstFile, $jpegQuality);
        break;
    case 3 :
        imagepng($dstHandle, $dstFile);
        break;
    default :
        $msg2='File Type Not Supported! ';
        return false;
    }

    imagedestroy($dstHandle);

    return true;
} 


?>