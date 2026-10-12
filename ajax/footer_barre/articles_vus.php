<?php
require_once($_SERVER['DOCUMENT_ROOT']."/global.php");

$TemplateAV = '';

$articles_vus = $main->articles_vus($s,6);
$c = 0;
if(count($articles_vus)>0){
    foreach($articles_vus as $artid){
		$c++;
		if($c>1) $TemplateAV.="<div class=\"sep\"></div>";
		$TemplateAV.=article_vu($artid);
    }
}
?>
<script type="text/javascript">
	$( document ).ready(function() {
		//Hover sur recap articles vus
		$('.artvu').each(function(){
			$(this).mouseenter(function(){
				$(this).css('background-color','#ab8d59');
				$(this).find('span.ref').css('color','white');
			}).mouseleave(function(){
				$(this).css('background-color','white');
				$(this).find('span.ref').css('color','#ab8d59');
			});
		});
	});
</script>
<p class="titre"><?php echo $template->translate('/footer/articles_consultes','F'); ?></p>
<?php echo stripslashes($TemplateAV); ?>
<div class="surelevation_blanc"></div>