<?php

require_once($_SERVER['DOCUMENT_ROOT']."/admin/admin_global.php");

// suivi utilisateur
if( isset($_GET['suiviid']) && isset($_GET['id']) ){

      //http://localhost.ma-cave-a-vin.fr/admin/ajax/delsuivi.php?id=28976&suiviid=xx&mailid=
			$checkSuivi = $DB_site2->query_first("SELECT * FROM suivi_client WHERE userid='".$_GET['id']."' AND id='".$_GET['suiviid']."'");
			if($checkSuivi){
  				// Delete suivi
   				$DB_site2->query("DELETE FROM suivi_client WHERE id = ".$_GET['suiviid']);
          //echo ("del ".$_GET['id'].";".$_GET['suiviid'].";".$_GET['mailid']);
			}

}

// mail inbox/outbox utilisateur
if( isset($_GET['mailid']) && isset($_GET['userid']) && isset($_GET['sens']) ){

    if($_GET['sens'] == 'in'){
      $table = 'mail_inbox';
    }elseif ($_GET['sens'] == 'out') {
      $table = 'mail_outbox';
    }
  	// Delete suivi
   	$DB_site2->query("DELETE FROM $table WHERE id = ".$_GET['mailid']);

}

// suivi fournisseur
if( isset($_GET['suiviid']) && isset($_GET['fournid']) ){

      //http://localhost.ma-cave-a-vin.fr/admin/ajax/delsuivi.php?fournid=Climadiff SA&suiviid=xx&mailid=
			$checkSuivi = $DB_site2->query_first("SELECT * FROM suivi_fournisseur WHERE fournisseur='".$_GET['fournid']."' AND id='".$_GET['suiviid']."'");
			if($checkSuivi){
  				// Delete suivi
   				$DB_site2->query("DELETE FROM suivi_fournisseur WHERE id = ".$_GET['suiviid']);
          //echo ("del ".$_GET['fournid'].";".$_GET['suiviid'].";".$_GET['mailid']);
			}

}

// mail inbox/outbox fournisseur
if( isset($_GET['mailid']) && isset($_GET['fournid']) ){

      //http://localhost.ma-cave-a-vin.fr/admin/ajax/delsuivi.php?fournid=Climadiff SA&suiviid=xx&mailid=
			$checkSuivi = $DB_site2->query_first("SELECT * FROM mail_inbox WHERE id= '".$_GET['mailid']."' AND suiviid='".$_GET['fournid']."'");
			if($checkSuivi){
  				// Delete suivi
   				$DB_site2->query("DELETE FROM mail_inbox WHERE id = ".$_GET['mailid']);
          //echo ("del ".$_GET['id'].";".$_GET['suiviid'].";".$_GET['mailid']);
			}
      
      	$checkSuivi = $DB_site2->query_first("SELECT * FROM mail_outbox WHERE id= '".$_GET['mailid']."' AND suiviid='".$_GET['fournid']."'");
			if($checkSuivi){
  				// Delete suivi
   				$DB_site2->query("DELETE FROM mail_outbox WHERE id = ".$_GET['mailid']);
          //echo ("del ".$_GET['id'].";".$_GET['suiviid'].";".$_GET['mailid']);
			}

}

?>