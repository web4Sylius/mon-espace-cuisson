<?php

include("../admin_global.php");
require_once("taskManager.class.php");

header("Cache-Control: no-cache");
header("Content-type:text/xml");

print "<?xml version=\"1.0\" encoding=\"iso-8859-1\"?>";
print "<xml>";

if(isset($_GET['fid']) && isset($_GET['delfile'])) $DB_site2->query("DELETE FROM suivi_interne_files WHERE id=".intval($_GET['fid'])." OR parent=".intval($_GET['fid']));
if(isset($_GET['fid']) && isset($_GET['newname'])) $DB_site2->query("UPDATE suivi_interne_files SET name='".strip_tags($_GET['newname'])."' WHERE id=".intval($_GET['fid']));
if(isset($_GET['name']) && isset($_GET['newfolder'])) $DB_site2->query("INSERT INTO suivi_interne_files SET taskid=".$_GET['taskid'].", parent=".intval($_GET['parent']).", date=".time().", utilisateur='".$user_info['username']."', name='".$_GET['name']."', state=1");
if(isset($_GET['fid']) && isset($_GET['moveto'])) $DB_site2->query("UPDATE suivi_interne_files SET parent=".$_GET['moveto']." WHERE id=".$_GET['fid']);

if($_GET['action']=="tree")
    print tree($_GET['taskid']);
else
    print getfiles($_GET['parent']);

print "</xml>";

function getfiles($parentid=null){
  global $DB_site2;
  if(!isset($_GET['taskid'])) return "";

  $tm=new taskManager($DB_site2,array(),$user_info['username']);
  $task=$tm->getTask($_GET['taskid']);

  $path="<path name=\"".str_replace('"',"",$task->name())."\" dataurl=\"taskmanager/fichiers.php?action=files&amp;taskid=".$_GET['taskid']."&amp;parent=0\" />";

  if(!is_null($parentid) && $parentid>0){
      $parent=$DB_site2->query_first("SELECT * FROM suivi_interne_files WHERE id=".$parentid);
      $path.="<path name=\"".str_replace('"',"",$parent['name'])."\" dataurl=\"taskmanager/fichiers.php?action=files&amp;taskid=".$_GET['taskid']."&amp;parent=".$parentid."\" />";
      $ret.="<parent id=\"$parent[parent]\" dataurl=\"taskmanager/fichiers.php?action=files&amp;taskid=".$_GET['taskid']."&amp;parent=$parent[parent]\" name=\"".str_replace("&","&amp;",$parent[name])."\" utilisateur=\"$parent[utilisateur]\" date=\"$parent[date]\" mod=\"rw\" />";
  }else
      $ret.="<parent id=\"\" name=\"\" utilisateur=\"\" date=\"\" mod=\"rw\" />";

  $sql="SELECT * FROM suivi_interne_files WHERE taskid=".$_GET['taskid'];
  if(!is_null($parentid)) $sql.=" AND parent=".$parentid;

  $r=$DB_site2->query($sql);
  while($f=$DB_site2->fetch_array($r)){
      if($f['ext']==""){
	  $count=$DB_site2->query_first("SELECT COUNT(*),SUM(size) FROM suivi_interne_files WHERE parent=".$f['id']);
	  $ret.="<folder id=\"$f[id]\" dataurl=\"taskmanager/fichiers.php?action=files&amp;taskid=".$_GET['taskid']."&amp;parent=".$f['id']."\" name=\"".str_replace("&","&amp;",$f[name])." (".$count[0].")\" utilisateur=\"$f[utilisateur]\" date=\"$f[date]\" size=\"".$count[1]."\" mod=\"rw\" />";
      }else
	  $ret.="<file id=\"$f[id]\" name=\"".str_replace("&","&amp;",$f[name])."\" path=\"taskmanager/fichiers/$f[id].$f[ext]\" ext=\"$f[ext]\" utilisateur=\"$f[utilisateur]\" date=\"$f[date]\" size=\"$f[size]\" mod=\"rw\" />";
  }

  $ret.="<upload dataurl=\"taskmanager/fichier_ul.php?taskid=".$_GET['taskid']."&amp;action=fb.load()&amp;parent=".intval($parentid)."\" />".$path;

  return $ret;
}

function tree($taskid){
    global $DB_site2;
    
    $ret="<folder id=\"0\" name=\"".$taskid."\" parent=\"-1\" />";

    $sql="SELECT * FROM suivi_interne_files WHERE taskid='".$taskid."' AND ext='' ORDER BY name";
    $r=$DB_site2->query($sql);
    while($f=$DB_site2->fetch_array($r)){
	$ret.="<folder id=\"$f[id]\" name=\"".str_replace("&","&amp;",$f[name])."\" parent=\"$f[parent]\" />";
    }

    return $ret;
}

?> 
