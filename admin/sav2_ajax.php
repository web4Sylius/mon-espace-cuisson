<?php

include("admin_global.php");

header("Cache-Control: no-cache");
header("Content-type:text/xml");

print "<?xml version=\"1.0\" encoding=\"iso-8859-1\"?>";
print "<xml>";

if(isset($_GET['action'])){
//      $_GET['value']=utf8_decode($_GET['value']);
     switch($_GET['action']){
	case "update":
	$field=$DB_site2->query_first("SELECT * FROM sav_fields WHERE savid='".intval($_GET['savid'])."' AND name='".mysqli_real_escape_string($DB_site2->link_id,$_GET['name'])."'");
	if($field['id']=="")
	    $DB_site2->query("INSERT INTO sav_fields SET savid='".intval($_GET['savid'])."', name='".mysqli_real_escape_string($DB_site2->link_id,$_GET['name'])."', value='".mysqli_real_escape_string($DB_site2->link_id,$_GET['value'])."', utilisateur='".$user_info['username']."', date=".time());
	else
	    $DB_site2->query("UPDATE sav_fields SET value='".mysqli_real_escape_string($DB_site2->link_id,$_GET['value'])."', utilisateur='".$user_info['username']."', date=".time()." WHERE id=".$field['id']);
	
	print "<field name=\"".$_GET['name']."\" value=\"".str_replace('"',"&quot;",$_GET['value'])."\" utilisateur=\"".$user_info['username']."\" date=\"".date("d/m/Y H:i")."\" />";

	case "delfile":
	if(isset($_GET['fid'])) $DB_site2->query("DELETE FROM sav_files WHERE id=".intval($_GET['fid']));
	print getfiles();
	break;
	case "rename":
	if(isset($_GET['fid']) && isset($_GET['newname'])) $DB_site2->query("UPDATE sav_files SET name='".strip_tags($_GET['newname'])."' WHERE id=".intval($_GET['fid']));
	print getfiles();
	break;
	case "files":
	print getfiles();
	break;

	case "comment":
	$DB_site2->query("UPDATE envoi_fournisseur SET comment='".addslashes($_GET['comment'])."' WHERE id=".$_GET['efid']);
	break;

	case "annulesfg":
	$contrat=$DB_site2->query_first("SELECT * FROM sfg WHERE facturid=".$_GET['facturid']);
	if($contrat['annule']==0 && !file_exists("sfg_pdf/".$_GET['facturid']."_annule.pdf")){
	    $cmd="composite -compose bumpmap ./images/annulesfg.png -density 100 ./sfg_pdf/".$_GET['facturid'].".pdf[0] ./sfg_pdf/".$_GET['facturid']."_annule.pdf";
	    exec($cmd);
	    $DB_site2->query("UPDATE sfg SET annule=".time()." WHERE facturid=".$_GET['facturid']);
	    $contrat['annule']=time();
	}
	print utf8_decode("<file id=\"".$_GET['facturid']."\" name=\"Contrat ".$_GET['facturid']." annule.pdf\" path=\"sfg_pdf/".$_GET['facturid']."_annule.pdf\" ext=\"pdf\" utilisateur=\"\" date=\"".$contrat['annule']."\" size=\"\" mod=\"ro\" />");
	break;
    }

}

print "</xml>";

function getfiles(){
  global $DB_site2;
  $sav=$DB_site2->query_first("SELECT sav.facturid,f.passefacture FROM sav INNER JOIN facture f USING(facturid) WHERE sav.id=".$_GET['savid']);
  if($sav!=""){
      $ret.="<file id=\"$sav[facturid]\" name=\"Facture $sav[facturid].pdf\" path=\"http://".$_SERVER['SERVER_NAME']."/facture2.php?facturid=$sav[facturid]&amp;passe=$sav[passefacture]&amp;nopub=1\" ext=\"pdf\" utilisateur=\"\" date=\"\" size=\"\" mod=\"ro\" />";
      $requete=$DB_site2->query("SELECT facturid,passefacture FROM facture WHERE provenance='sav' AND provenanceid=".$sav['facturid']);
      while($fsav=$DB_site2->fetch_array($requete)){
	  $ret.="<file id=\"$fsav[facturid]\" name=\"Facture $fsav[facturid].pdf\" path=\"http://".$_SERVER['SERVER_NAME']."/facture2.php?facturid=$fsav[facturid]&amp;passe=$fsav[passefacture]&amp;nopub=1\" ext=\"pdf\" utilisateur=\"\" date=\"\" size=\"\" mod=\"ro\" />";
      }
      
      $r=$DB_site2->query("SELECT factureid,lignefactureid,libelle FROM lignefacture lf INNER JOIN facture f ON (f.facturid=lf.factureid) WHERE lf.artcode LIKE 'ACI-KDO%' AND provenance='sav' AND provenanceid=$sav[facturid]");
      while($ligne=$DB_site2->fetch_array($r)){
	  $ret.="<file id=\"$ligne[lignefactureid]\" name=\"".$ligne['libelle']."\" path=\"http://".$_SERVER['SERVER_NAME']."/admin/cheque_cadeau.php?facturid=$ligne[factureid]&amp;lignefactureid=$ligne[lignefactureid]\" ext=\"pdf\" utilisateur=\"\" date=\"\" size=\"\" mod=\"ro\" />";
      }
  }
  $r=$DB_site2->query("SELECT * FROM sav_files WHERE savid=".intval($_GET['savid']));
  while($f=$DB_site2->fetch_array($r)){
      $ret.="<file id=\"$f[id]\" name=\"$f[name]\" path=\"sav/$f[id].$f[ext]\" ext=\"$f[ext]\" utilisateur=\"$f[utilisateur]\" date=\"$f[date]\" size=\"$f[size]\" mod=\"rw\" />";
  }

  return $ret;
}

?>
