<?php

require ("global.php");
unset($_GET['userid']);

$temp="ClientSatisfaction";
if(isset($_POST['ldo'])){
  $userid=$user_info['userid'];
  if(isset($_GET['v']) && md5("userid".$_GET['uid'])==$_GET['v']) $userid=$_GET['uid'];
  $sql="INSERT INTO satisfaction SET date=".time().", userid=".$userid;
  $_POST['raison_achat']=implode(",",$_POST['raison_achat']);
  foreach($_POST as $key=>$value) $sql.=", `$key`='".mysqli_real_escape_string($DB_site->link_id,$value)."'";
  $result=$DB_site->query($sql);

  $acceptfile=array("jpg","jpeg","png","gif","pdf");
  if(isset($_FILES['photo'])){
    $extension = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
    if(!in_array($extension,$acceptfile)){
	$msg2="Type de fichier interdit";
    }else{
      $DB_site->query("INSERT INTO user_files SET userid='".$userid."', date=".time().", utilisateur='', name='".strip_tags($_FILES['photo']['name'])."', size=".$_FILES['photo']['size'].", ext='$extension'");
      $id=$DB_site->insert_id();
      if(!move_uploaded_file($_FILES['photo']['tmp_name'], "admin/userfiles/".$id.".".$extension)){
              $msg2="Votre fichier n'a pas pu être envoyé";
	      $DB_site->query("UPDATE user_files SET state=-1 WHERE id=$id");
      }else
	$DB_site->query("UPDATE user_files SET state=1 WHERE id=$id");
	$msg2="Envoi fichier ".strip_tags($_FILES['photo']['name'])." terminé";
    }
  }

  if($result) $temp="ClientSatisfaction2";
}

if(isset($_GET['facturid'])){
  if(!isset($_GET['v']) || md5("userid".$_GET['uid'])!=$_GET['v']) header("location:/");
  $template->parts["facturid"]=intval($_GET['facturid']);
}elseif(isset($userid)){
  $f=$DB_site->query_first("SELECT facturid FROM facture WHERE userid=".$userid." AND valide IN (5,8) ORDER BY datefacture DESC");
  $template->parts["facturid"]=$f[0];
}else
  header("location:/");

$template->parts["fil"]="<a href=\"/\">".$template->translate("/site/nom")."</a> &gt; <a href=\"client.php\">".$template->translate("/client/mon_compte")."</a> &gt; ".$template->translate("/client/enquete_satisfaction");
$template->evaltemplate($temp,"content");
$template->evaltemplate("ClientPage","main");
$template->pageparams($template->translate("/site/url"),$template->translate("/client/enquete_satisfaction"),"");
$template->out();

?>
