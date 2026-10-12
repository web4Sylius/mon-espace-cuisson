<?php

    
$today=getdate();
$vhost=gethostbyaddr($_SERVER['REMOTE_ADDR']);
$cid=0;
$usid=0;
if(isset($_COOKIE['tr'])) $cid=$_COOKIE['tr'];
if(isset($_COOKIE['userid'])) $usid=intval($_COOKIE['userid']);


$ac=false;

if(!isset($nostat)){
    $req="SELECT id,user,referant FROM stat WHERE ip='".$_SERVER['REMOTE_ADDR']."' AND (timestamp > ".mktime(0,0,0).")";
    $srow=$DB_site->query_first($req);
    $statid=$srow['id'];


    if($srow['id']=="")
		$ac=true;
    elseif($srow['user']==0 && $usid!=0){
		$DB_site->query("UPDATE stat SET user=".$usid." WHERE id=".$statid);
    }

    if($ac){
      $referer="";
      if(isset($_SERVER['HTTP_REFERER'])) $referer=$_SERVER['HTTP_REFERER'];
      
      $req="INSERT INTO `stat` SET ip='".$_SERVER['REMOTE_ADDR']."',referant='".mysqli_real_escape_string($DB_site->link_id,$referer)."',agent='".mysqli_real_escape_string($DB_site->link_id,$_SERVER['HTTP_USER_AGENT'])."',host='$vhost',timestamp=".time().",cid='".$cid."', user='".$usid."'";

      $result=$DB_site->query($req);
      $statid=$DB_site->insert_id();

      if($cid==0){
		  setcookie("tr",$statid,(time()+3600 * 24 * 30 * 365));
		  $DB_site->query("UPDATE stat SET cid='".$statid."' WHERE id=".$statid);
		  $cid=$statid;
      }
    }

    $page=$_SERVER['REQUEST_URI'];
    $blocips=$DB_site->query("SELECT * FROM bloc_ip WHERE ip LIKE '".$_SERVER['REMOTE_ADDR']."%'");
    if($DB_site->num_rows($blocips)>0){
        while($blocip=$DB_site->fetch_array($blocips)){
		  $DB_site->query("UPDATE bloc_ip SET hit=hit+1 WHERE ip='".$blocip['ip']."'");
        }
		$req="INSERT INTO stat_page SET page='".$page."',timestamp=".time().", statid=".$statid.", status='403'";
		$result=$DB_site->query($req);
		sleep(30);
		header("HTTP/1.0 404 Not Found");
		exit;
    }
	
	if(!isset($_SERVER["REDIRECT_STATUS"]) || $_SERVER["REDIRECT_STATUS"] ==""){
		$redirect_status=200;
	}else {
		$redirect_status=$_SERVER["REDIRECT_STATUS"];
	}
	if($_SERVER["SCRIPT_NAME"]=="/ajax/loadAjax.php"){	
		// $req="INSERT INTO stat_page SET page='".mysql_real_escape_string($page)."',detail='".$template->langue."',timestamp=".time().", statid=".$statid.", status='".$_SERVER["REDIRECT_STATUS"]."'";
		$req="INSERT INTO stat_page SET page='".mysqli_real_escape_string($DB_site->link_id,$_GET['r'])."',detail='".$template->langue."',timestamp=".time().", statid=".$statid.", status='".$redirect_status."'";
		$result=$DB_site->query($req);
	}
    if($redirect_status!=200){ 
      // $req="INSERT INTO stat_page SET page='".mysql_real_escape_string($page)."',detail='".$template->langue."',timestamp=".time().", statid=".$statid.", status='".$_SERVER["REDIRECT_STATUS"]."'";
      $req="INSERT INTO stat_page SET page='".mysqli_real_escape_string($DB_site->link_id,$_SERVER['REQUEST_URI'])."',detail='".$template->langue."',timestamp=".time().", statid=".$statid.", status='".$redirect_status."'";
      $result=$DB_site->query($req);
    }
}

function isIE($versions=array()) {
    if (!empty($versions))
        $versions = '('.implode('|',$versions).')';
    else
        $versions = '1?\d';
    $ua = $_SERVER['HTTP_USER_AGENT'];
    $is_not_opera = false===stripos($ua, 'opera');
    return $is_not_opera && preg_match('/\bmsie '.$versions.'\./i', $ua);
}


?>