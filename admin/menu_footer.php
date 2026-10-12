<?php
require "admin_global.php";
include "./header.php";
include "./menu.php";
include($rootpath."class/fonctions.php");
include($rootpath."class/categorie.class.php");


$langue ='FR';
$langues = array("FR","EN"); 
//traitement POST (activation / Désactivation menu )
if(isset($_POST['activemenu'])){
	$menuid = $_POST['activemenu'];
	$menu = $DB_site2->query_first("SELECT actif FROM menu_footer WHERE menuid ='".$menuid."'");
	if($menu[0] == 0)
		$newEtat = 1;
	else
		$newEtat = 0;

	$DB_site2->query("UPDATE menu_footer SET actif =".$newEtat." WHERE menuid='".$menuid."' ");
}

//traitement POST (suppression d'une page)
if(isset($_POST['del'])){
	$DB_site2->query("DELETE FROM menu_footer_pages WHERE pageid ='".$_POST['pageid']."'");
}

//traitements POST (ajout d'une page a un menu)
if(isset($_POST['ajoutpage'])){
	$menuid = intval($_POST['menuid']);
	$search = array("https://".$host_conf->http_host."/","https://www.my-wine-cabinet.com");
	$type = $_POST['type'];
	$pro = intval($_POST['pro']);
	$lib = $pageurl = array();
	$pagepro = false;
	if($pro == 1) $pagepro = true;

		foreach ($langues as $l) {
		//ecriture des URL + libelle pour insertion dans la BDD
			$lib[$l] 	 = mysqli_real_escape_string($DB_site2->link_id,$_POST['page'.$l]);
			$rewriteUrl = str_replace($search, "/", $_POST['url'.$l]);
			$pageurl[$l] = mysqli_real_escape_string($DB_site2->link_id,$rewriteUrl);
		}
		//on verifie si la page n'as pas déja été ajoutée
		$verif = $DB_site2->query_first("SELECT pageid FROM menu_footer_pages WHERE urlFR ='".$pageurl['FR']."' AND type ='".$type."' and pro=".$pro);
		if(!$verif){
			
			$lastPosition = $DB_site2->query_first("SELECT pageid FROM menu_footer_pages WHERE menuid=".$menuid." ORDER BY position DESC LIMIT 1");
			$newPosition = $lastPosition[0] + 1;

			$DB_site2->query("INSERT INTO menu_footer_pages SET menuid =".$menuid.", type ='".$type."', urlFR='".$pageurl['FR']."',libelleFR='".$lib['FR']."', urlEN='".$pageurl['EN']."',libelleEN='".$lib['EN']."', pro=".$pro.", position ='".$newPosition."'");
		}else{
			$error .= "la page éxiste déjà pour ce menu !";
			echo "<script>alert('".$error."');</script>";	
		}
}

?>

<script type="text/javascript">
	$ = jQuery ;
	function del(pageid, type,pro){
		$('#del_pageid').val(pageid);
		document.forms.formu_del.submit();
	}
	function activeMenu(menuid){
		document.getElementById('formu_active_'+menuid).submit();
	}
</script>

<h2>Administration des menus footer</h2>
<div class="col-xs-12">
<?php
$langue = "FR";
$template->langue = $langue;
$menus = $DB_site2->query("SELECT * FROM menu_footer");

while($menu = $DB_site2->fetch_array($menus)){
	$url = "";
	$pagespart = array();
	$pagespro = array();
	$checked="";
	$menuid = $menu['menuid'];
	$menutitre = $menu['titre'.$langue];
	$menutype = $menu['type'];
	if( intval($menu['actif']) == 1) $checked = "checked";

	$pages = $DB_site2->query("SELECT * FROM menu_footer_pages WHERE menuid ='".$menu['menuid']."' AND type ='".$menu['type']."' ORDER BY position ASC");
	while($page = $DB_site2->fetch_array($pages)){
		if($page['pro'] == 0)
			$pagespart[] = $page;
		else
			$pagespro[] = $page;
	}

	echo "<div class=\"col-xs-12 menufooter_parent\" id=\"menu_".$menuid."\">";
	echo "<div class=\"col-xs-10\"><h3>".$menutitre." (".$menu['type'].")</h3></div>";

	//Bouton ON / OFF
	echo "<div class=\"col-xs-2\">";
	echo "<form id=\"formu_active_".$menuid."\" method=\"post\" action\"#\">";
	echo "<div class=\"onoffswitch\">
			<input type=\"checkbox\" name=\"onoffswitch\" class=\"onoffswitch-checkbox\" id=\"myonoffswitch".$menuid."\" onclick=\"activeMenu(".$menuid.");\" $checked>
    		<label class=\"onoffswitch-label\" for=\"myonoffswitch".$menuid."\">
        		<span class=\"onoffswitch-inner\"></span>
        		<span class=\"onoffswitch-switch\"></span>
    		</label>
		</div>";
	echo "<input type=\"hidden\" name=\"activemenu\" value=\"".$menuid."\" >";	
	echo "</form>";	
	echo "</div>";	
	echo 	"<div class=\"col-xs-6 menufooter\">";
	echo "<h2>Menu site Particulier</h2>";
	echo 		"<ul>";

	foreach ($pagespart as $lapage) {
		echo "<li onmouseover\"\">".$lapage['libelle'.$langue]." <img class=\"del\" src=\"images/supprimer.gif\" onclick=\"del(".$lapage['pageid'].");\" title=\"Supprimer\"></li>";
	}

	echo 		"</ul>";
	echo 	"</div>";
	echo 	"<div class=\"col-xs-6 menufooter\" style=\"border-left:1px dashed black;\">";
	echo "<h2>Menu site Pro</h2>";
	echo 		"<ul>";

	foreach ($pagespro as $lapage) {
		echo "<li onmouseover\"\">".$lapage['libelle'.$langue]." <img class=\"del\" src=\"images/supprimer.gif\" onclick=\"del(".$lapage['pageid'].");\" title=\"Supprimer\"></li>";
	}

	echo 		"</ul>";
	echo  "</div>";
	echo 	"<div class=\"col-xs-12\">"	;

	//formulaire ajout nouvelle page particulier
	echo 	"<div class=\"col-xs-6 ajoutpage\" >";
	echo 	"<form name=\"formu_".$menu['type']."\" method=\"post\" action=\"#\">";
	echo 	"<input type=\"hidden\" name=\"ajoutpage\" value=\"1\">";
	echo 	"<input type=\"hidden\" name=\"type\" value=\"".$menu['type']."\">";
	echo 	"<input type=\"hidden\" name=\"menuid\" value=\"".$menu['menuid']."\">";
	echo 	"<input type=\"hidden\" name=\"pro\" value=\"0\">";
	echo 	"<input type=\"hidden\" id=\"libelle\" name=\"libelle\" value=\"\">";

	//echo 	"<select name=\"page\"><option></option>".$option."</select>";
	echo 	"<div class=\"col-xs-12 pluspage\">";
	echo 	"<h3>Ajout d'une nouvelle page </h3>";
	echo 	"<div class=\"col-xs-3\">libelleFR</div><div class=\"col-xs-9\"><input type=\"text\" name=\"pageFR\" ></div>";
	echo 	"<div class=\"col-xs-3\">libelleEN</div><div class=\"col-xs-9\"><input type=\"text\" name=\"pageEN\" ></div>";
	echo 	"<div class=\"col-xs-3\">urlFR <small>(1)</small></div><div class=\"col-xs-9\"><input type=\"text\" name=\"urlFR\" ></div>";
	echo 	"<div class=\"col-xs-3\">urlEN <small>(1)</small></div><div class=\"col-xs-9\"><input type=\"text\" name=\"urlEN\" ></div>";
	echo 	"<a class=\"col-xs-offset-4 col-xs-4\" onclick=\"document.forms.formu_".$menu['type'].".submit();\">Ajouter</a>";
	echo 	"</div>";
	echo 	"<small>(1) : ne pas mettre https://www.ma-cave-a-vin.fr ou https://www.my-wine-cabinet.com. Mettre directement /cave-vin/cave-a-vin-de-vieillissement.htm</small>";
	echo 	"</form></div>";

	//formualaire ajout nouvelle page pro
	echo 	"<div class=\"col-xs-6 ajoutpage\" style=\"border-left:1px dashed black;\">";
	echo 	"<form name=\"formu_".$menu['type']."_pro\" method=\"post\" action=\"#\">";
	echo 	"<input type=\"hidden\" name=\"ajoutpage\" value=\"2\">";
	echo 	"<input type=\"hidden\" name=\"type\" value=\"".$menu['type']."\">";
	echo 	"<input type=\"hidden\" name=\"menuid\" value=\"".$menu['menuid']."\">";
	echo 	"<input type=\"hidden\" name=\"pro\" value=\"1\">";

	//echo 	"<select name=\"page\"><option></option>".$optionpro."</select>";
	echo 	"<div class=\"col-xs-12 pluspage\">";
	echo 	"<h3>Ajout d'une nouvelle page </h3>";
	echo 	"<div class=\"col-xs-3\">libelleFR</div><div class=\"col-xs-9\"><input type=\"text\" name=\"pageFR\" ></div>";
	echo 	"<div class=\"col-xs-3\">libelleEN</div><div class=\"col-xs-9\"><input type=\"text\" name=\"pageEN\" ></div>";
	echo 	"<div class=\"col-xs-3\">urlFR <small>(1)</small></div><div class=\"col-xs-9\"><input type=\"text\" name=\"urlFR\" ></div>";
	echo 	"<div class=\"col-xs-3\">urlEN <small>(1)</small></div><div class=\"col-xs-9\"><input type=\"text\" name=\"urlEN\" ></div>";
	echo 	"<a class=\"col-xs-offset-4 col-xs-4\" onclick=\"document.forms.formu_".$menu['type']."_pro.submit();\">Ajouter</a>";
	echo 	"</div>";	
	echo 	"<small>(1) : ne pas mettre https://www.ma-cave-a-vin.fr ou https://www.my-wine-cabinet.com. Mettre directement /cave-vin/cave-a-vin-de-vieillissement.htm</small>";
	echo 	"</form></div>";

	echo 	"<form name=\"formu_del\" method=\"post\" action=\"#\">";
	echo 	"<input type=\"hidden\" name=\"del\" value=\"1\">";
	echo 	"<input type=\"hidden\" id=\"del_pageid\" name=\"pageid\" value=\"\">";
	echo 	"</form>";
	echo  	"</div>";

	echo "</div>";


}
?>
</div>