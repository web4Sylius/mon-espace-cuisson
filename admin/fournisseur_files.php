<?php

include("admin_global.php");
include("sav.class.php");
include("../class/stock.class.php");

$basefolders=$DB_site2->query_first("SELECT COUNT(*) FROM fournisseur_files WHERE fournisseur='".mysqli_real_escape_string($DB_site2->link_id,$_GET['fournisseur'])."' AND parent=0 AND ext='' AND stock_cmd_id=0");
if($basefolders[0]==0) $DB_site2->query("INSERT INTO fournisseur_files (fournisseur,parent,date,utilisateur,name,state) VALUES ('".mysqli_real_escape_string($DB_site2->link_id,$_GET['fournisseur'])."',0,".time().",'','Tarifs',1),('".mysqli_real_escape_string($DB_site2->link_id,$_GET['fournisseur'])."',0,".time().",'','Contrats',1),('".mysqli_real_escape_string($DB_site2->link_id,$_GET['fournisseur'])."',0,".time().",'','CR réunion',1)");

header("Cache-Control: no-cache");
header("Content-type:text/xml");

print "<?xml version=\"1.0\" encoding=\"utf-8\"?>";
print "<xml>";

$table=isset($_GET['savid'])?"sav_files":"fournisseur_files";

if(isset($_GET['fid']) && isset($_GET['delfile'])){
    switch($_GET['action']){
    case "":
    case "files":
    case "stock":
      $DB_site2->query("DELETE FROM $table WHERE id=".intval($_GET['fid'])." OR parent=".intval($_GET['fid']));
    break;
  
    case "article":
    $DB_site2->query("DELETE FROM article_files WHERE id=".intval($_GET['fid'])." OR parent=".intval($_GET['fid']));
    break;
    }
}
if(isset($_GET['fid']) && isset($_GET['newname'])){
    switch($_GET['action']){
    case "":
    case "files":
    case "stock":
    $DB_site2->query("UPDATE $table SET name='".mysqli_real_escape_string($DB_site2->link_id,strip_tags($_GET['newname']))."' WHERE id=".intval($_GET['fid']));
    break;
    
    case "article":
    $DB_site2->query("UPDATE article_files SET name='".mysqli_real_escape_string($DB_site2->link_id,strip_tags($_GET['newname']))."' WHERE id=".intval($_GET['fid']));
    break;
    }
}
if(isset($_GET['name']) && isset($_GET['newfolder'])){
    switch($_GET['action']){
    case "":
    case "files":
    case "stock":
    if($table=="fournisseur_files")
	$sql="INSERT INTO fournisseur_files SET fournisseur='".mysqli_real_escape_string($DB_site2->link_id,$_GET['fournisseur'])."', stock_cmd_id=".intval($_GET['cmdid']).", parent=".intval($_GET['parent']).", date=".time().", utilisateur='".$user_info['username']."', name='".mysqli_real_escape_string($DB_site2->link_id,$_GET['name'])."', state=1";
    else
	$sql="INSERT INTO sav_files SET savid=".mysqli_real_escape_string($DB_site2->link_id,$_GET['savid']).", parent=".intval($_GET['parent']).", date=".time().", utilisateur='".$user_info['username']."', name='".mysqli_real_escape_string($DB_site2->link_id,$_GET['name'])."', state=1";
    $DB_site2->query($sql);
    break;
    
    case "article":
    $DB_site2->query("INSERT INTO article_files SET artid='".$_GET['artid']."', parent=".intval($_GET['parent']).", date=".time().", utilisateur='".$user_info['username']."', name='".mysqli_real_escape_string($DB_site2->link_id,$_GET['name'])."', state=1");
    break;
    }
}
if(isset($_GET['fid']) && isset($_GET['moveto'])){
    $DB_site2->query("UPDATE $table SET parent=".$_GET['moveto']." WHERE id=".$_GET['fid']);
}


print getfiles($_GET['parent']);

print "</xml>";

function getfiles($parentid=null){
  global $DB_site2;
  
  if(isset($_GET['fournisseur']))
    $path="<path name=\"".stripslashes($_GET['fournisseur'])."\" dataurl=\"fournisseur_files.php?action=files&amp;fournisseur=".rawurlencode($_GET['fournisseur'])."&amp;parent=0\" />";
    
    
  switch($_GET['action']){
  case null:
  case "":
  case "files":
    if(!isset($_GET['savid'])){
        $ret.=getfournfiles($_GET['fournisseur'],$parentid);
        if($parentid==0){
  	      $f=$DB_site2->query_first("SELECT * FROM fournisseur WHERE societe='".mysqli_real_escape_string($DB_site2->link_id,$_GET['fournisseur'])."'");
      	  if($f['marques']!=""){
      	      $count=$DB_site2->query_first("SELECT COUNT(artid) FROM article_marque INNER JOIN article_files USING(artid) WHERE marqueid IN (".$f['marques'].") GROUP BY artid");
      	      $ret.="<folder id=\"0\" dataurl=\"fournisseur_files.php?action=articles&amp;fournisseur=".rawurlencode($_GET['fournisseur'])."\" name=\"Articles (".$count[0].")\" utilisateur=\"\" date=\"\" size=\"\" mod=\"r\" />";
      	  }
        }
        if(!is_null($parentid) && $parentid>0){
      	  $parent=$DB_site2->query_first("SELECT * FROM fournisseur_files WHERE id=".$parentid." AND stock_cmd_id=0");
      	  $path.="<path name=\"".$parent['name']."\" dataurl=\"fournisseur_files.php?action=files&amp;fournisseur=".rawurlencode(stripslashes($_GET['fournisseur']))."&amp;parent=".$parentid."\" />";
      	  $ret.="<parent id=\"$parent[parent]\" dataurl=\"fournisseur_files.php?action=files&amp;fournisseur=".rawurlencode(stripslashes($_GET['fournisseur']))."&amp;parent=$parent[parent]\" name=\"".str_replace("&","&amp;",$parent[name])."\" utilisateur=\"$parent[utilisateur]\" date=\"$parent[date]\" mod=\"rw\" />";
        }else
  	      $ret.="<parent id=\"\" name=\"\" utilisateur=\"\" date=\"\" mod=\"rw\" />";
        
        $ret.="<upload dataurl=\"fournisseur_ul.php?fournisseur=".rawurlencode($_GET['fournisseur'])."&amp;action=fb.load()&amp;parent=".intval($parentid)."\" />".$path;
    }else{
        $sav=new sav($DB_site2,$_GET['savid']);
        
        $ret.="<path name=\"Commande ".$sav->facturid."\" dataurl=\"fournisseur_files.php?action=stock&amp;fournisseur=".rawurlencode($stock_cmd->destinataire)."&amp;parent=0&amp;cmdid=".$sav->facturid."\" />
        <path name=\""."SAV ".$_GET['savid']."\" dataurl=\"fournisseur_files.php?action=files&amp;savid=".$_GET['savid']."&amp;parent=0\" />";
        $ret.=getsavfiles($sav->id,$parentid);
        
        $stock_cmd=new stock_cmd($DB_site2,$sav->facturid);
        if($parentid==0){
  	      $ret.="<parent id=\"0\" dataurl=\"fournisseur_files.php?action=stock&amp;fournisseur=".rawurlencode($stock_cmd->destinataire)."&amp;parent=0&amp;cmdid=".$sav->facturid."\" name=\"\" utilisateur=\"\" date=\"\" mod=\"rw\" />";
        }else{
  	      $folder=$DB_site2->query_first("SELECT * FROM sav_files WHERE id=".$parentid);
      	  $ret.="<path name=\"".$folder['name']."\" dataurl=\"fournisseur_files.php?savid=".$sav->id."&amp;parent=".$parentid."&amp;cmdid=".$sav->facturid."\" />";
      	  $ret.="<parent id=\"0\" dataurl=\"fournisseur_files.php?savid=".$sav->id."&amp;parent=".$folder['parent']."&amp;cmdid=".$sav->facturid."\" name=\"\" utilisateur=\"\" date=\"\" mod=\"rw\" />";
        }
        
        $ret.="<upload dataurl=\"sav2_ul.php?savid=".$_GET['savid']."&amp;action=fb.load()&amp;parent=".$parentid."\" />";
    }
  break;

  case "articles":
    $ret.=getarticles($_GET['fournisseur']);
    $path.="<path name=\"Articles\" dataurl=\"fournisseur_files.php?action=articles&amp;fournisseur=".rawurlencode($_GET['fournisseur'])."\" />";
    $ret.="<parent id=\"0\" dataurl=\"fournisseur_files.php?action=files&amp;fournisseur=".rawurlencode($_GET['fournisseur'])."\" name=\"Articles\" utilisateur=\"\" date=\"\" mod=\"r\" />";
    $ret.="<upload dataurl=\"\" />".$path;
  break;

  case "article":
    $ret.=getartid($_GET['artid'],$_GET['parent']);
    $article=$DB_site2->query_first("SELECT artcode FROM article WHERE artid=".$_GET['artid']);
    $path.="<path name=\"Articles\" dataurl=\"fournisseur_files.php?action=articles&amp;fournisseur=".rawurlencode($_GET['fournisseur'])."\" />
    <path name=\"".$article['artcode']."\" dataurl=\"fournisseur_files.php?action=article&amp;artid=".$_GET['artid']."&amp;fournisseur=".rawurlencode($_GET['fournisseur'])."\" />";
    if(isset($_GET['parent'])){
      $parent=$DB_site2->query_first("SELECT * FROM article_files WHERE id=".$_GET['parent']);
      $path.="<path name=\"".$parent['name']."\" dataurl=\"fournisseur_files.php?action=article&amp;artid=".$_GET['artid']."&amp;parent=".$parent['id']."&amp;fournisseur=".rawurlencode($_GET['fournisseur'])."\" />";
      $ret.="<parent id=\"0\" dataurl=\"fournisseur_files.php?action=article&amp;artid=".$_GET['artid']."&amp;fournisseur=".rawurlencode($_GET['fournisseur'])."\" name=\"Articles\" utilisateur=\"\" date=\"\" mod=\"rw\" />";
    }else{
      $ret.="<parent id=\"0\" dataurl=\"fournisseur_files.php?action=articles&amp;fournisseur=".rawurlencode($_GET['fournisseur'])."\" name=\"Articles\" utilisateur=\"\" date=\"\" mod=\"rw\" />";
    }
    $ret.="<upload dataurl=\"article_ul.php?artid=".$_GET['artid']."&amp;action=fb.load()&amp;parent=".intval($_GET['parent'])."\" />".$path;
  break;
  
  case "tree":
    if(!isset($_GET['savid']))
        $ret.=tree($_GET['fournisseur']);
    else
        $ret.=sav_tree($_GET['savid']);
  break;

  case "stock":
    if(isset($_GET['mailid'])){
      $ret .=fichierMail($DB_site2, $_GET['mailid'], $_GET['mail']);
      $ret.="<parent id=\"".$_GET['stock_cmd_id']."\" dataurl=\"fournisseur_files.php?fournisseur=".rawurlencode($_GET['fournisseur'])."&amp;action=stock&amp;cmdid=".$_GET['stock_cmd_id']."&amp;parent=0\" utilisateur=\"\" date=\"".time()."\" mod=\"rw\" />";
    }else{
      if($_GET['parent'] == 0) $ret.=dossierMailFourn($DB_site2,rawurlencode(stripslashes($_GET['fournisseur'])),'stock',$_GET['cmdid']);
      $ret.=stock_files($_GET['cmdid'],$_GET['parent']);
      $path="<path name=\"".$_GET['cmdid']."\" dataurl=\"fournisseur_files.php?fournisseur=".rawurlencode(trim($_GET['fournisseur']))."&amp;cmdid=".$_GET['cmdid']."&amp;action=stock&amp;parent=0\" />";
      if(!is_null($parentid) && $parentid>0){
          $parent=$DB_site2->query_first("SELECT * FROM fournisseur_files WHERE id=".$parentid." AND stock_cmd_id=".$_GET['cmdid']);
          $path.="<path name=\"".$parent['name']."\" dataurl=\"fournisseur_files.php?action=stock&amp;fournisseur=".rawurlencode(stripslashes($_GET['fournisseur']))."&amp;parent=".$parentid."&amp;cmdid=".$_GET['cmdid']."\" />";
          $ret.="<parent id=\"$parent[parent]\" dataurl=\"fournisseur_files.php?action=stock&amp;fournisseur=".rawurlencode(stripslashes($_GET['fournisseur']))."&amp;parent=$parent[parent]&amp;cmdid=".$_GET['cmdid']."\" name=\"".str_replace("&","&amp;",$parent[name])."\" utilisateur=\"$parent[utilisateur]\" date=\"$parent[date]\" mod=\"rw\" />";
      }else
          $ret.="<parent id=\"\" name=\"\" utilisateur=\"\" date=\"\" mod=\"rw\" />";
      $ret.="<upload dataurl=\"fournisseur_ul.php?fournisseur=".rawurlencode($_GET['fournisseur'])."&amp;action=fb.load()&amp;parent=".intval($parentid)."&amp;cmdid=".$_GET['cmdid']."\" />".$path;
    }
  break;
  }

  return $ret;
}

function getfournfiles($fournisseur,$parent){
  global $DB_site2;

  $sql="SELECT * FROM fournisseur_files WHERE fournisseur='".mysqli_real_escape_string($DB_site2->link_id,$fournisseur)."' AND stock_cmd_id=0";
  if(!is_null($parent)) $sql.=" AND parent=".$parent;
  $sql.=" ORDER BY name";
  $r=$DB_site2->query($sql);
  $ret="";
  while($f=$DB_site2->fetch_array($r)){
      if($f['ext']==""){
	  $count=$DB_site2->query_first("SELECT COUNT(*),SUM(size) FROM fournisseur_files WHERE parent=".$f['id']." AND stock_cmd_id=0");
	  $ret.="<folder id=\"$f[id]\" dataurl=\"fournisseur_files.php?action=files&amp;fournisseur=".rawurlencode($_GET['fournisseur'])."&amp;parent=".$f['id']."\" name=\"".str_replace("&","&amp;",$f[name])." (".$count[0].")\" utilisateur=\"$f[utilisateur]\" date=\"$f[date]\" size=\"".$count[1]."\" mod=\"rw\" />";
      }else
	  $ret.="<file id=\"$f[id]\" name=\"".str_replace("&","&amp;",$f[name])."\" path=\"fournfiles/dl.php?fid=".$f['id']."\" ext=\"$f[ext]\" utilisateur=\"$f[utilisateur]\" date=\"$f[date]\" size=\"$f[size]\" mod=\"rw\" />";
  }
  return $ret;
}

function getsavfiles($savid,$parent){
  global $DB_site2;
  
  $sql="SELECT * FROM sav_files WHERE savid=".intval($savid);
  if(!is_null($parent)) $sql.=" AND parent=".$parent;
  $sql.=" ORDER BY name";
  $r=$DB_site2->query($sql);
  $ret="";
  while($f=$DB_site2->fetch_array($r)){
      if($f['ext']==""){
	  $count=$DB_site2->query_first("SELECT COUNT(*),SUM(size) FROM sav_files WHERE parent=".$f['id']);
	  $ret.="<folder id=\"$f[id]\" dataurl=\"fournisseur_files.php?action=files&amp;savid=".$savid."&amp;parent=".$f['id']."\" name=\"".str_replace("&","&amp;",$f[name])." (".$count[0].")\" utilisateur=\"$f[utilisateur]\" date=\"$f[date]\" size=\"".$count[1]."\" mod=\"rw\" />";
      }else
	  $ret.="<file id=\"$f[id]\" name=\"".str_replace("&","&amp;",$f[name])."\" path=\"sav/".$f['id'].".".$f['ext']."\" ext=\"$f[ext]\" utilisateur=\"$f[utilisateur]\" date=\"$f[date]\" size=\"$f[size]\" mod=\"rw\" />";
  }
  return $ret;
}

function getarticles($fournisseur){
    global $DB_site2;
    $ret="";
    $f=$DB_site2->query_first("SELECT * FROM fournisseur WHERE societe='".mysqli_real_escape_string($DB_site2->link_id,$_GET['fournisseur'])."'");
    if($f['marques']=="") return "";
    
    $req=$DB_site2->query("SELECT artid,artcode,artcode_eurocave FROM article_marque INNER JOIN article_files USING(artid) INNER JOIN article USING(artid) WHERE marqueid IN (".$f['marques'].") GROUP BY artid");
    while($art=$DB_site2->fetch_array($req)){
	$ret.="<folder id=\"$f[id]\" dataurl=\"fournisseur_files.php?action=article&amp;artid=".$art['artid']."&amp;fournisseur=".rawurlencode($_GET['fournisseur'])."\" name=\"".str_replace("&","&amp;",$art['artcode']." (".$art['artcode_eurocave'].")")."\" utilisateur=\"\" date=\"\" size=\"\" mod=\"r\" />";
    }

    return $ret;
}


function getartid($artid,$parent){
  global $DB_site2;

  $sql="SELECT * FROM article_files WHERE artid=".$artid;
  if(!is_null($parent)) $sql.=" AND parent=".$parent;
  $r=$DB_site2->query($sql);
  $ret="";
  while($f=$DB_site2->fetch_array($r)){
      if($f['ext']==""){
	  $count=$DB_site2->query_first("SELECT COUNT(*),SUM(size) FROM article_files WHERE parent=".$f['id']);
	  $ret.="<folder id=\"$f[id]\" dataurl=\"fournisseur_files.php?action=article&amp;fournisseur=".rawurlencode($_GET['fournisseur'])."&amp;artid=".$_GET['artid'].(isset($_GET['artid2'])?"&amp;artid2=$artid":"")."&amp;parent=".$f['id']."\" name=\"".str_replace("&","&amp;",$f[name])." (".$count[0].")\" utilisateur=\"$f[utilisateur]\" date=\"$f[date]\" size=\"".$count[1]."\" mod=\"rw\" />";
      }else
	  $ret.="<file id=\"$f[id]\" name=\"".str_replace("&","&amp;",$f[name])."\" path=\"articlefiles/$f[id].$f[ext]\" ext=\"$f[ext]\" utilisateur=\"$f[utilisateur]\" date=\"$f[date]\" size=\"$f[size]\" mod=\"rw\" />";
  }
  return $ret;
}

function tree($fournisseur){
    global $DB_site2;
    
    $ret="<folder id=\"0\" name=\"".$fournisseur."\" parent=\"-1\" />";

    $sql="SELECT * FROM fournisseur_files WHERE fournisseur='".mysqli_real_escape_string($DB_site2->link_id,$fournisseur)."' AND ext='' AND stock_cmd_id=0 ORDER BY name";
    $r=$DB_site2->query($sql);
    while($f=$DB_site2->fetch_array($r)){
	$ret.="<folder id=\"$f[id]\" name=\"".str_replace("&","&amp;",$f[name])."\" parent=\"$f[parent]\" />";
    }

    return $ret;
}

function sav_tree($savid){
    global $DB_site2;
    
    $ret="<folder id=\"0\" name=\"SAV ".$savid."\" parent=\"-1\" />";

    $sql="SELECT * FROM sav_files WHERE savid=".$savid." AND ext='' ORDER BY name";
    $r=$DB_site2->query($sql);
    while($f=$DB_site2->fetch_array($r)){
	$ret.="<folder id=\"$f[id]\" name=\"".str_replace("&","&amp;",$f[name])."\" parent=\"$f[parent]\" />";
    }

    return $ret;
}

function stock_files($cmdid,$parent){
      global $DB_site2;
      $savtypes=array(0=>"transport-installation",1=>"panne",2=>"erreur produit",3=>"rétractation",4=>"manque produit");

      $cmdid=intval($cmdid);

      $sql="SELECT * FROM fournisseur_files WHERE stock_cmd_id=".$cmdid;
      if(!is_null($parent)) $sql.=" AND parent=".$parent;
      $sql.=" ORDER BY name";
      $r=$DB_site2->query($sql);
      $ret="";
      while($f=$DB_site2->fetch_array($r)){
	  if($f['ext']==""){
	      $count=$DB_site2->query_first("SELECT COUNT(*),SUM(size) FROM fournisseur_files WHERE parent=".$f['id']." AND stock_cmd_id=".$cmdid);
	      $ret.="<folder id=\"$f[id]\" dataurl=\"fournisseur_files.php?action=stock&amp;fournisseur=".rawurlencode($_GET['fournisseur'])."&amp;parent=".$f['id']."&amp;cmdid=$cmdid\" name=\"".str_replace("&","&amp;",$f[name])." (".$count[0].")\" utilisateur=\"$f[utilisateur]\" date=\"$f[date]\" size=\"".$count[1]."\" mod=\"rw\" />";
	  }else
	      $ret.="<file id=\"$f[id]\" name=\"".str_replace("&","&amp;",$f[name])."\" path=\"fournfiles/dl.php?fid=".$f['id']."\" ext=\"$f[ext]\" utilisateur=\"$f[utilisateur]\" date=\"$f[date]\" size=\"$f[size]\" mod=\"rw\" />";
      }
      if($parent==0){
	  $sav=new sav($DB_site2);
	  $savlist=$sav->get_from_facturid($cmdid);
	  if($savlist!=false){
	      foreach($savlist as $savcmd){
		  $ret.="<folder id=\"".$savcmd->id."\" name=\"SAV ".$savtypes[$savcmd->typelitige]."\" dataurl=\"fournisseur_files.php?savid=".$savcmd->id."\" img=\"images/sav.png\" utilisateur=\"\" date=\"".time()."\" size=\"0\" mod=\"ro\" />";
	      }
	  }
      }
      return $ret;
}

function dossierMailFourn($DB,$fournName,$type,$typeid){
  $ret = "";

  switch ($type) {
    case 'stock':
      $sqlwhere = " AND stock_cmd_id ='".$typeid."' AND facturid=0 AND savid=0 AND projetid=0";
      $folder="stock_cmd_id";
      break;      
  }
  
  $req=$DB->query("SELECT * FROM mail_inbox WHERE 1".$sqlwhere." ORDER BY date DESC");
  while($mail = $DB->fetch_array($req)){
    $year = date("Y", $mail['date']);
    $date = date("Ymd-H:i", $mail['date']);
    $name = $date."-E";
    $req2 = $DB->query_first("SELECT COUNT(*) FROM mail_attachment WHERE moyen = 'mail_in' AND mailid ='".$mail['id']."'");
    if($req2[0] > 0){
      $ret.="<folder id=\"".$mail['id']."\" dataurl=\"fournisseur_files.php?fournisseur=".rawurlencode($fournName)."&amp;$folder=$typeid&amp;mail=in&amp;mailid=".$mail['id']."&amp;action=stock\" name=\"$name\" mod=\"r\" />";
    }
  }
  $req=$DB->query("SELECT * FROM mail_outbox WHERE 1".$sqlwhere." ORDER BY date DESC");
  while($mail = $DB->fetch_array($req)){
    $year = date("Y", $mail['date']);
    $date = date("Ymd-H:i", $mail['date']);
    $name = $date."-S";
    $req2 = $DB->query_first("SELECT COUNT(*) FROM mail_attachment WHERE moyen = 'mail_out' AND mailid ='".$mail['id']."'");
    if($req2[0] > 0){
      $ret.="<folder id=\"".$mail['id']."\" dataurl=\"fournisseur_files.php?fournisseur=".rawurlencode($fournName)."&amp;$folder=$typeid&amp;mail=out&amp;mailid=".$mail['id']."&amp;action=stock\" name=\"$name\" mod=\"r\" />";
    }
  }

  return $ret;
}

function fichierMail($DB,$mailid,$type){
  global $rootpath;
  global $host;

  $ret="";
  switch ($type) {
    case 'in':
      $table = "mail_inbox";
      $moyen = "mail_in";
      break;
    case 'out':
      $table = "mail_outbox";
      $moyen = "mail_out";
      break;
    default:
      $table = "mail_inbox";
      $moyen = "mail_in";
      break;
  }

  $req = $DB->query("SELECT ma.*, m.utilisateur, m.date FROM mail_attachment as ma INNER JOIN $table as m ON m.id=ma.mailid  WHERE ma.moyen = '$moyen' AND ma.mailid ='$mailid'");
  while($pj = $DB->fetch_array($req)) {
    $year = date("Y", $pj['date']);         
    $ret.="<file id=\"".$pj['id']."\" name=\"".$pj['name']."\" path=\"/configurations/".$host."/mails/attachments/".$year."/".$pj['id'].".".$pj['ext']."\" ext=\"".$pj['ext']."\" utilisateur=\"".$pj['utilisateur']."\" date=\"".$mail_in['date']."\" size=\"".$pj['size']."\" mod=\"rw\" />";
  }

  return $ret;
}

?>
