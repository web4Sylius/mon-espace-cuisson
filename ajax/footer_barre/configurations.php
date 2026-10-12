<?php
require_once($_SERVER['DOCUMENT_ROOT']."/global.php");

$c=new configurateur($DB_site);
$conflist=$c->get_list($s,$_COOKIE['admin_username'],5);
$confnb=count($conflist);
if($confnb>0){
    $template->parts["hidestart"]="hidden";
    $template->parts["conflist"]="<ul>";
    foreach($conflist as $conf){
	$confimg="";
	if($conf->artids){
	    $confimg="<div class=\"clear\"></div>";
	    foreach(array_slice($conf->artids,0,5) as $conf_artid){
		$article=$DB_site->query_first("SELECT artcode,libelle".$template->langue." AS libelle FROM article WHERE artid=".$conf_artid);
		$url="/art,".$conf_artid.",".url_rewrite($article['libelle']).".htm";
		$confimg.="<a class=\"img\" href=\"".$url."\" title=\"".$article['libelle']." ".$article['artcode']."\"><img src=\"/images/".$conf_artid."_40_40.jpg\"></a>";
	    }
	    $confimg.="<div class=\"clear\"></div>";
	}
	$template->parts["conflist"].="<li><a href=\"/configurateur.php?id=".$conf->configurateurid."\">".date("d/m/Y H:i",$conf->date)."</a>".$confimg."</li>";
    }
    $template->parts["conflist"].="</ul>";
}
?>
<p class="titre"><?php echo $template->translate('/footer/configurateur_mcav','F'); ?></p>
<?php echo stripslashes($template->parts["conflist"]); ?>
<a href="/configurateur.php" class="start [?hidestart]"><?php echo $template->translate('/footer/configuration_go','F'); ?></a>