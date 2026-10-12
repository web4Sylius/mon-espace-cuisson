<?php
ini_set('memory_limit', '600M');
include "admin_global.php";
include "./header.php";
include "./menu.php";


$superusers = array('philippe', 'ariel', "cc", "benoit");

if(!in_array($user_info['username'],$superusers)) exit;

if(isset($_POST['del_mail'])){
	$type = $_POST['type'];
	foreach ($_POST as $key => $value) {
		if(strpos($key, "del_".$type."_") !== false){
			$DB_site2->query("DELETE FROM mail_to_check WHERE id = '$value'");
		}
	}
}

?>
<ul class="liens_rapides" style="background-color: #497168; border: none;font-weight: 600;">
  <li><a class="js-scrollTo" href="#sortants" style="text-decoration: none;color:white;">SORTANTS</a></li>
  <li><a class="js-scrollTo" href="#entrants" style="text-decoration: none;color:white;">ENTRANTS</a></li>
</ul>
<script>
var $jq = jQuery.noConflict();
  $jq(document).ready(function() {
    $jq('.js-scrollTo').each(function() {
      var page = $jq(this).attr('href');
      if ($jq(page).length==0) {
        $jq(this).hide();
      }
    });
    $jq('.js-scrollTo').on('click', function() { // Au clic sur un élément
      var page = $jq(this).attr('href'); // Page cible
      var speed = 200; // Durée de l'animation (en ms)
      $jq('html, body').animate( { scrollTop: $jq(page).offset().top }, speed ); // Go
      return false;
    });
  });
</script>

<?php
//mail sortants
$mails_sortants = $DB_site2->query("SELECT * FROM mail_to_check WHERE type='mail_out' ORDER BY id DESC");
$nb_email_sortants = $DB_site2->num_rows($mails_sortants);
?>
<h3 class="h3_tri">Emails sortants (<?php echo $nb_email_sortants; ?>)</h3>
<div id="sortants" class="col-xs-12 no-padding">
	<div class="col-xs-10" style="background-color: #c7e0f5;">
		<div class="col-xs-2 no-padding">Date</div>
		<div class="col-xs-4">Sujet</div>
		<div class="col-xs-3">Expéditeur</div>
		<div class="col-xs-3">Destinataire</div>
	</div>
	<div class="col-xs-2" style="background-color: #c7e0f5;color:#c7e0f5;">coco</div>
<?php

while ($sortant = $DB_site2->fetch_array($mails_sortants)) {

	$id_s = $sortant['id'];

	echo "<div id=\"ligne_".$sortant['id']."\" class=\"col-xs-10 no-padding lignetri\" onclick=\"view_content(".$sortant['id'].");\">";
	echo 	"<div class=\"col-xs-2 no-padding\">".date("d/m/Y H:i", $sortant['date'])."</div>";
	echo 	"<div class=\"col-xs-4 no-padding\">".Truncate($sortant['subject'],35, true)."</div>";
	echo 	"<div class=\"col-xs-3 no-padding\">".$sortant['from']."</div>";
	echo 	"<div class=\"col-xs-3 no-padding\">".$sortant['to']."</div>";
	echo "</div>";

	echo "<div class=\"col-xs-2 no-padding lignetri2\">";
	echo 	"<input name=\"del_mail_out\" type=\"checkbox\" value=\"".$sortant['id']."\">";
	echo "</div>";
	
	echo "<div id=\"".$sortant['id']."\" class=\"col-xs-12 no-padding lignetri_hide\">";
	
	echo "</div>";
}

?>
<div class="col-xs-10"></div>
<div class="col-xs-2 no-padding"><button onclick="del_mail('mail_out');" style="width: 100%;">Supprimer</button></div>
</div>
<div class="clear"></div>

<?php 
$mails_entrants = $DB_site2->query("SELECT * FROM mail_to_check WHERE type='mail_in' ORDER BY id DESC");
$nb_email_entrants = $DB_site2->num_rows($mails_entrants);
?>

<h3 class="h3_tri">Emails entrants (<?php echo $nb_email_entrants; ?>)</h3>
<div id="entrants" class="col-xs-12 no-padding">
	<div class="col-xs-10" style="background-color: #c7e0f5;">
		<div class="col-xs-2 no-padding">Date</div>
		<div class="col-xs-4">Sujet</div>
		<div class="col-xs-3">Expéditeur</div>
		<div class="col-xs-3">Destinataire</div>
	</div>
	<div class="col-xs-2" style="background-color: #c7e0f5;"></div>


<?php

while ($entrant = $DB_site2->fetch_array($mails_entrants)) {

	echo "<div id=\"ligne_".$entrant['id']."\" class=\"col-xs-10 no-padding lignetri\" onclick=\"view_content(".$entrant['id'].");\">";
	echo 	"<div class=\"col-xs-2 no-padding\">".date("d/m/Y H:i", $entrant['date'])."</div>";
	echo 	"<div class=\"col-xs-4 no-padding\">".Truncate($entrant['subject'],35, true)."</div>";
	echo 	"<div class=\"col-xs-3 no-padding\">".$entrant['from']."</div>";
	echo 	"<div class=\"col-xs-3 no-padding\">".$entrant['to']."</div>";
	echo "</div>";

	echo "<div class=\"col-xs-2 no-padding lignetri2\">";
	echo 	"<input name=\"del_mail_in\" type=\"checkbox\" value=\"".$entrant['id']."\">";
	echo "</div>";

	echo "<div id=\"".$entrant['id']."\" class=\"col-xs-12 no-padding lignetri_hide\">";

	echo "</div>";
} 

?>	
<div class="col-xs-10"></div>
<div class="col-xs-2 no-padding"><button onclick="del_mail('mail_in');" style="width: 100%;">Supprimer</button></div>
</div>

<form id="del_mail_out" method="post">
	<input type="hidden" name="del_mail" value="1">
	<input type="hidden" id="del_type" name="type" value="mail_out">
	
</form>

<form id="del_mail_in" method="post">
	<input type="hidden" name="del_mail" value="1">
	<input type="hidden" id="del_type" name="type" value="mail_in">
	
</form>

<?php







include "./footer.php";


?>
<script type="text/javascript">

	function view_content(id){
		jQuery.ajax({
			url : 'mail_tri_ajax.php',
			type: 'GET',
			data: 'mailid='+id,
			dataType: 'html',
			success: function(data, statut, xhr){
				jQuery('#'+id).append(data);
			}
		});
		jQuery("#ligne_"+id).toggleClass("lignetri_select");
		jQuery("#"+id).toggle();
	}

	

	function del_mail(type){
		var elm = document.getElementsByName("del_"+type);
		var parent = document.getElementById('del_'+type);
		for (var i=0;i<elm.length;i++) {
			if(elm[i].checked){
				input = document.createElement("input"); 
				input.type = "hidden";
				input.name = "del_"+type+"_"+elm[i].value;
				input.value = elm[i].value;
				parent.appendChild(input);
			}
		}
		document.getElementById("del_"+type).submit();
	}
</script>



<?php

function Truncate($string, $length, $stopanywhere=false) {
    //truncates a string to a certain char length, stopping on a word if not specified otherwise.
    $string=html_entity_decode($string);
    if (strlen($string) > $length) {
        //limit hit!
        $string = substr($string,0,($length -3));
        if ($stopanywhere) {
            //stop anywhere
            $string .= '...';
        } else{
            //stop on a word.
            $string = substr($string,0,strrpos($string,' ')).'...';
        }
    }
    return $string;
}
?>