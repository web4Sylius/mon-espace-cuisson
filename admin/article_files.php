<?php

include("admin_global.php");

$fileinfos=array("notice"=>"Notice","instruction"=>"Instructions montage");
foreach($langues as $l){
    $fileinfos["notice".$l]="Notice ".$l;
    $fileinfos["instruction".$l]="Instructions ".$l;
}
ksort($fileinfos);

header("Cache-Control: no-cache");
header("Content-type:text/xml");

print "<?xml version=\"1.0\" encoding=\"iso-8859-1\"?>";
print "<xml>";

if(isset($_GET['fid']) && isset($_GET['delfile'])){
   $file = $DB_site2->query_first("SELECT * FROM article_files WHERE id=".intval($_GET['fid']));
   $DB_site2->query("DELETE FROM article_files WHERE id=".intval($_GET['fid'])." OR parent=".intval($_GET['fid']));
   unlink("../configurations/$host/articlefiles/".intval($file['id']).".".$file['ext']);
} 
if(isset($_GET['fid']) && isset($_GET['newname'])) $DB_site2->query("UPDATE article_files SET name='".mysqli_real_escape_string($DB_site2->link_id,strip_tags($_GET['newname']))."' WHERE id=".intval($_GET['fid']));
if(isset($_GET['name']) && isset($_GET['newfolder'])) $DB_site2->query("INSERT INTO article_files SET artid=".(isset($_GET['artid2'])?$_GET['artid2']:$_GET['artid']).", parent=".intval($_GET['parent']).", date=".time().", utilisateur='".$user_info['username']."', name='".$_GET['name']."', state=1");
if(isset($_GET['fid']) && isset($_GET['info'])) $DB_site2->query("UPDATE article_files SET infos='".mysqli_real_escape_string($DB_site2->link_id,$_GET['info'])."' WHERE id=".intval($_GET['fid']));
if(isset($_GET['fid']) && isset($_GET['moveto'])) $DB_site2->query("UPDATE article_files SET parent='".intval($_GET['moveto'])."' WHERE id=".intval($_GET['fid']));


if($_GET['action']=="tree"){
    print "<folder id=\"0\" name=\"Article\" parent=\"-1\" />";
    $req2=$DB_site2->query("SELECT * FROM article_files WHERE artid=".$_GET['artid']." AND ext='' ORDER BY name");
    while($folder=$DB_site2->fetch_array($req2)){
        print "<folder id=\"".$folder['id']."\" name=\"".$folder['name']."\" img=\"\" utilisateur=\"".$folder['utilisateur']."\" date=\"".$folder['date']."\" size=\"0\" mod=\"rw\" parent=\"".$folder['parent']."\" />";
    }
}else{
    foreach($fileinfos as $info=>$libelle)
        print "<fileinfo name=\"".$libelle."\" code=\"".$info."\"/>";
    print getfiles($_GET['parent']);
}

print "</xml>";

function getfiles($parentid=null){
    global $DB_site2;
    global $fileinfos;
    if(!isset($_GET['artid'])) return "";

    $artid=$_GET['artid'];
    if(isset($_GET['artid2'])){
        $artid=$_GET['artid2'];
    }

    $art=$DB_site2->query_first("SELECT artcode FROM article WHERE artid=".$_GET['artid']);
    $path="<path name=\"".$art['artcode']."\" dataurl=\"article_files.php?action=files&amp;artid=".$_GET['artid']."&amp;parent=0\" />";
    if(isset($_GET['artid2'])){
        $art=$DB_site2->query_first("SELECT artcode FROM article WHERE artid=".$_GET['artid2']);
        $path.="<path name=\"".$art['artcode']."\" dataurl=\"article_files.php?action=files&amp;artid=".$_GET['artid']."&amp;artid2=".$_GET['artid2']."&amp;parent=0\" />";
    }

    $ret.=getartid($artid,$parentid);

    if(!is_null($parentid) && $parentid>0){
        $parent=$DB_site2->query_first("SELECT * FROM article_files WHERE id=".$parentid);
        $path.="<path name=\"".$parent['name']."\" dataurl=\"article_files.php?action=files&amp;artid=".$_GET['artid'].(isset($_GET['artid2'])?"&amp;artid2=".$_GET['artid2']:"")."&amp;parent=".$parentid."\" />";
        $ret.="<parent id=\"$parent[parent]\" dataurl=\"article_files.php?action=files&amp;artid=".$_GET['artid'].(isset($_GET['artid2'])?"&amp;artid2=".$_GET['artid2']:"")."&amp;parent=$parent[parent]\" name=\"".str_replace("&","&amp;",$parent[name])."\" utilisateur=\"$parent[utilisateur]\" date=\"$parent[date]\" mod=\"rw\" />";
    }else
        if(isset($_GET['artid2']))
        $ret.="<parent id=\"".$_GET['artid']."\" dataurl=\"article_files.php?action=files&amp;artid=".$_GET['artid']."\" name=\"\" utilisateur=\"\" date=\"\" mod=\"rw\" />";
        else
        $ret.="<parent id=\"\" name=\"\" utilisateur=\"\" date=\"\" mod=\"rw\" />";

    
    $r=$DB_site2->query("SELECT a.artid,a.artcode FROM article_lot al INNER JOIN article a USING(artid) WHERE lotid=".$artid);
    while($art=$DB_site2->fetch_array($r)){
        $count=$DB_site2->query_first("SELECT COUNT(*),SUM(size) FROM article_files WHERE artid=".$art['artid']);
        $ret.="<folder id=\"".$art['artid']."\" dataurl=\"article_files.php?action=files&amp;artid=".$_GET['artid']."&amp;artid2=".$art['artid']."&amp;parent=0\" name=\"".$art['artcode']." (".$count[0].")\" img=\"../configurations/".$host."/images/produits/br/".$art['artid'].".jpg\" utilisateur=\"\" date=\"".time()."\" size=\"".$count[1]."\" mod=\"ro\" />";
    }

    $ret.="<upload dataurl=\"article_ul.php?artid=$artid&amp;action=fb.load()&amp;parent=".intval($parentid)."\" />".$path;

    return $ret;
}

function getartid($artid,$parent){
    global $DB_site2;
    global $fileinfos;
    global $host;

    $sql="SELECT * FROM article_files WHERE artid=".$artid;
    if(!is_null($parent)) $sql.=" AND parent=".$parent;
    $r=$DB_site2->query($sql);
    $ret="";
    while($f=$DB_site2->fetch_array($r)){
        if($f['ext']==""){
            $count=$DB_site2->query_first("SELECT COUNT(*),SUM(size) FROM article_files WHERE parent=".$f['id']);
            $ret.="<folder id=\"$f[id]\" dataurl=\"article_files.php?action=files&amp;artid=".$_GET['artid'].(isset($_GET['artid2'])?"&amp;artid2=$artid":"")."&amp;parent=".$f['id']."\" name=\"".str_replace("&","&amp;",$f[name])." (".$count[0].")\" utilisateur=\"$f[utilisateur]\" date=\"$f[date]\" size=\"".$count[1]."\" mod=\"rw\" />";
        }else{
            $ret.="<file id=\"$f[id]\" name=\"".str_replace("&","&amp;",$f[name])."\" path=\"/configurations/$host/articlefiles/$f[id].$f[ext]\" ext=\"$f[ext]\" utilisateur=\"$f[utilisateur]\" date=\"$f[date]\" size=\"$f[size]\" mod=\"rw\" infos=\"".$f['infos']."\" />";
        }
    }
    return $ret;
}

?>
