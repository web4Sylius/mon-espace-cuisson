<?php
@ini_set('display_errors','off');
ob_start('ob_gzip');

$a = $_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'];
$b = explode('/',$a);
$c = '';//Initialization
for ($i=0;$i<count($b)-1;$i++)
{
	$c .= $b[$i].'/';//and
}
define('TYPE','gif,jpg,png');//The definition of the type system allows the upload
define('ADDR','http://'.$c.'images/');//picture address
define('UPLOAD',dirname(__FILE__).'/images/');//upload
//Character then the function
function randStr($len)
{
    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
    $string = '';
    for(;$len>=1;$len--)
    {
        $string .= substr($chars,rand()%strlen($chars),1);
    }
    return $string;
}
//Upload Function
function upload($check)
{
	if (!is_dir(UPLOAD))
	{
		if (!mkdir(UPLOAD, 0777,TRUE) || !chmod(UPLOAD, 0777)) exit('Create upload directory failed!');
	}
	if (isset($_FILES['logo']['tmp_name']) && !empty($_FILES['logo']['tmp_name']))
	{
		if($_FILES['logo']['error'] > 0)
		{
			exit('Upload failed!');//Upload failed
		}
		else
		{
			$filename = addslashes($_FILES['logo']['name']);//Client-side file name
				$type = explode('.',$filename);//Take the suffix
			if (!ereg($type[count($type)-1],$check)) exit('System does not allow file types!');//Test the legality of uploading the file type
				$filename_sever = randStr(8) . '.' . $type[count($type)-1];
			if(!move_uploaded_file($_FILES['logo']['tmp_name'],UPLOAD.$filename_sever)) exit('Upload failed!');
			return $filename_sever;
		}
	}
}

$step = isset($_GET['step']) ? htmlentities($_GET['step']) : '';
$Submit = isset($_POST['Submit']) ? htmlentities($_POST['Submit']) : '';
//Add
if ($step == 'add' AND $Submit == 'Submit'){
	$id = (count(file('gb.dat')) < 1) ? 1 : count(file('gb.dat'))+1;
	$site = htmlentities($_POST['site']);
	$logo = upload(TYPE);//Test the legality of uploading the file type
	$cancel_return  = htmlentities($_POST['cancel_return']);
	$notify_url = htmlentities($_POST['notify_url']);
	$return = htmlentities($_POST['return']);
	$fp = fopen('gb.dat','a');//Write-only mode in order to open the gb.dat text file, the file pointer to the file tail.
	$str = $site.'|'.$logo.'|'.$cancel_return.'|'.$notify_url.'|'.$return.'|'.$id."\n";//"|"When used as a data partitioning symbolic interval data.
	fwrite($fp,$str);//Writing data to files
	fclose($fp);
	header('location:?step=show&rand='.rand());
	exit();
}
//Edite
if($step == 'edite' AND $Submit == 'Submit'){
	$id = is_numeric($_GET['id']) ? $_GET['id'] : exit('ERROR!');//Security authentication
	$content = array();
	$list1 = file('gb.dat');
	$n = count($list1);
	for ($i=0;$i<$n;$i++)
	{
		$list2 = explode('|',$list1[$i]);
		if(eregi($id,$list2[5]))
		{
			$content = $list2;
			break;
		}
	}
	if ($n>0)
	{
		$fp=fopen('gb.dat','w');
		$id = intval($content[5]);//To avoid wrap turn the whole
		$site = htmlentities($_POST['site']);
		$logo = $_FILES['logo']['error'] > 0 ? $content[1] : upload(TYPE);//Test the legality of uploading the file type
		$cancel_return  = htmlentities($_POST['cancel_return']);
		$notify_url = htmlentities($_POST['notify_url']);
		$return = htmlentities($_POST['return']);
		for ($i=0;$i<$n;$i++)
		{
		$list2 = explode('|',$list1[$i]);
			if(eregi($content[5],$list2[5]))
			{
				$list1[$i] = $site.'|'.$logo.'|'.$cancel_return.'|'.$notify_url.'|'.$return.'|'.$id."\n";
				break;
			}
		}
	}
	for($i=0;$i<$n;$i++)
	{
		fwrite($fp,$list1[$i]);
	}
	fclose($fp);
	header('location:?step=show&rand='.rand());
	exit();
}
//Del
if($step == 'del')
{

	$id = is_numeric($_GET['id']) ? $_GET['id'] : exit('ERROR!');//Security authentication
	$list1 = file('gb.dat');//Reading gb.dat array $list
	$n = count($list1);//$_List the contents of the calculation where the total number of records and give the variable $n
	if ($n>0)
	{//If the record number is greater than 0
		$fp = fopen('gb.dat','w');//write-only mode to open the file
		for ($i=0;$i<$n;$i++)
		{
			$list2 = explode('|',$list1[$i]);
			if(eregi($id,$list2[5]))
			{//Will be sent over $site with the array $list [$i] match inside the string comparison
				$list1[$i] = '';//If the match successful, will be $ list [$i] empty (to remove the purposes)
				break;
			}
		}
		for($i=0;$i<$n;$i++)
		{
			fwrite($fp,$list1[$i]);
		}
		fclose($fp);
		if (file_exists('images/'.$list2[1])) @unlink('images/'.$list2[1]);
		header('location:?step=show&rand='.rand());//To prevent the caching
		exit();
	}
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=gb2312" />
<meta name="robots" content="noindex,nofollow">
<meta name="googlebot" content="noindex,nofollow,noarchive">
<title>Multi-Site Manager</title>
<script language="javascript" src="ajax.js" type="text/javascript"></script>
<script language="javascript" type="text/javascript">
function check(obj,Param,bool)
{
	var result = Param.test(obj.value);
	if (bool == 1) result = !result;//URL
	if(result)
	{//Other
		for(;obj.value.substr(0,1) == '/';)
		{
			obj.value = obj.value.replace('/','');
		}
		if (bool == 1) obj.value = obj.value+'/';//URL
	}
	else
	{
		if (bool == 1)
		{//URL
			for(;obj.value.substr(obj.value.length-1) == '/';)
			{
				obj.value = obj.value.substr(0,obj.value.length-1);
			}
			obj.value = obj.value+'/';
		}
	}
}
</script>
<style type="text/css">
*{font-size:13px}
.input{border:0px #FFFFFF solid; width:100%;background-color:#DDDDDD}
.textarea{border:0px #FFFFFF solid;background-color:#DDDDDD}
table,tr,td{border-collapse:collapse}
img{max-width:370px;max-height:95px}
.red {
	border:1px solid red;
	color:red;
}
</style>
</head>
<body>
<span id="ajax"></span>
<?php
//Add
if ($step == 'add')
{
?>
<form action="?step=add" method="post" enctype="multipart/form-data" name="form1" id="form1">
  <table width="100%" border="0" cellspacing="0" cellpadding="0">
    <tr>
      <td width="10%">Domain:</td>
      <td width="90%"><input name="site" type="text" id="site" value="http://www." size="50" onblur="return check(this,/[\/]$/,1);" /><font color="#FF0000">(Must be based on "/" at the end of,Like "http://www.fanghuowangzhi.com/")</font></td>
    </tr>
    <tr>
      <td>LOGO:</td>
      <td><input name="logo" type="file" id="logo" size="50" /></td>
    </tr>
    <tr>
      <td>cancel_return:</td>
      <td><input name="cancel_return" type="text" id="cancel_return" value="index.php" size="50" onblur="return check(this,/^\//,0);" /></td>
    </tr>
    <tr>
      <td>notify_url:</td>
      <td><input name="notify_url" type="text" id="notify_url" value="modules/paypal2/validation.php" size="50" onblur="return check(this,/^\//,0);" /></td>
    </tr>
    <tr>
      <td>return:</td>
      <td><input name="return" type="text" id="return" value="order-confirmation.php" size="50" onblur="return check(this,/^\//,0);" /></td>
    </tr>
    <tr>
      <td colspan="2"><input type="submit" name="Submit" id="Submit" value="Submit" /></td>
    </tr>
  </table>
</form>
<?php
}
//Edite
elseif($step == 'edite')
{
	$id = is_numeric($_GET['id']) ? $_GET['id'] : exit('ERROR!');//Security authentication
	$content = array();
	$list1 = file('gb.dat');
	$n = count($list1);
	for ($i=0;$i<$n;$i++)
	{
		$list2 = explode('|',$list1[$i]);
		if(eregi($id,$list2[5]))
		{
			$content = $list2;
			break;
		}
	}
?>
<form action="?step=edite&id=<?php echo $id; ?>" method="post" enctype="multipart/form-data" name="form1" id="form1">
  <table width="100%" border="0" cellspacing="0" cellpadding="0">
    <tr>
      <td width="10%">Domain:</td>
      <td width="90%"><input name="site" type="text" id="site" value="<?php echo $content[0]?>" size="50" onblur="return check(this,/[\/]$/,1);" /></td>
    </tr>
    <tr>
      <td>LOGO:</td>
      <td><input name="logo" type="file" id="logo" value="<?php echo $content[1]?>" size="50" />
        <font color="#FF0000">(If you do not need to change, please leave blank)</font></td>
    </tr>
    <tr>
      <td>cancel_return:</td>
      <td><input name="cancel_return" type="text" id="cancel_return" value="<?php echo $content[2]?>" size="50" onblur="return check(this,/^\//,0);" /></td>
    </tr>
    <tr>
      <td>notify_url:</td>
      <td><input name="notify_url" type="text" id="notify_url" value="<?php echo $content[3]?>" size="50" onblur="return check(this,/^\//,0);" /></td>
    </tr>
    <tr>
      <td>return:</td>
      <td><input name="return" type="text" id="return" value="<?php echo $content[4]?>" size="50" onblur="return check(this,/^\//,0);" /></td>
    </tr>
    <tr>
      <td colspan="2"><input type="submit" name="Submit" id="Submit" value="Submit" /></td>
    </tr>
  </table>
</form>
<?php
}
//Show
else
{
?>
<table width="100%" border="1" cellpadding="0" cellspacing="0">
  <tr>
    <th width="2%" height="36" align="center">id</th>
    <th width="21%" align="center">Site </th>
    <th width="31%" align="center">LOGO </th>
    <th width="9%" align="center">cancel_return </th>
    <th width="15%" align="center">notify_url </th>
    <th width="13%" align="center">return </th>
    <th width="3%" align="center">Add </th>
    <th width="3%" align="center">Edit </th>
    <th width="3%" align="center">Del </th>
  </tr>
  <?php
	$mypage = '';//begin
	if (file_exists('gb.dat'))
	{
		$array=file('gb.dat');
		$arr=array_reverse($array);
		$num=count($array);
		if ($num>0)
		{
			$pagesize = 100;
			$page = isset($_GET['page']) ? htmlentities($_GET['page']) : 1;
			$total=ceil($num/$pagesize);
			if($page<1)
			{
				$page=1;
			}
			$number=($page-1)*$pagesize;
			for($i=0;$i<=$pagesize-1;$i++)
			{
				$row=explode('|',$arr[$number]);
				list($site,$logo,$cancel_return,$notify_url,$return,$id)=$row;
				?>
  <tr <?php if($i % 2 == 0){?> style="background-color:#F6F6F6"<?php }?>>
    <td align="center"><?php echo $id?></td>
    <td align="center"><?php echo $site?></td>
    <td align="center"><img src="images/<?php echo $logo ?>" /></td>
    <td align="center"><?php echo $cancel_return?></td>
    <td align="center"><?php echo $notify_url?></td>
    <td align="center"><?php echo $return?></td>
    <td align="center"><a href="javascript:getclass('ajax','?step=add&rand=<?php echo rand();?>');">Add</a></td>
    <td align="center"><a href="javascript:getclass('ajax','?step=edite&id=<?php echo $id?>&rand=<?php echo rand();?>');">Edit</a></td>
    <td align="center"><a href="?step=del&id=<?php echo $id?>&rand=<?php echo rand();?>" onclick="return(confirm('Together with the right to delete pictures,To confirm the deletion?'))">Del</a></td>
  </tr>
  <?
				if ($number == $num-1)
				{
					break;
				}
				$number = $number + 1;
			}
			if ($page <> 1)
			{
				$back = $page - 1;
				$mypage = '<a href=?step=show&page=1><font color="#FF0000">First</font></a>';
				$mypage .= " <a href=?step=show&page=$back><font color='#FF0000'>Previous</font></a>" ;
			}
			if ($page <> $total)
			{
				$next = $page + 1;
				$mypage .= " <a href=?step=show&page=$next><font color='#FF0000'>Next</font></a>" ;
				$mypage .= " <a href=?step=show&page=$total><font color='#FF0000'>Last</font></a>";
			}
			$mypage .= "Pages:<font color='#FF0000'>$page</font> / <font color='#FF0000'>$total</font>";
			$mypage .= "Total <font color='#FF0000'>$num</font> Records";
		}
		else
		{
			$mypage .=  '<center>At present there is no record of!Please<a href="?step=add">Add</a></center>';
		}
	}
	else
	{
		$mypage .=  '<center>Data file is missing, please contact the administrator!</center>';
	}
?>
</table>
<br />
<?php echo $mypage;
}
?>
</body>
</html>
<?php
ob_end_flush();

function ob_gzip($content){
	if(!headers_sent()&&extension_loaded("zlib")&&strstr($_SERVER["HTTP_ACCEPT_ENCODING"],"gzip")){
		$content = gzencode($content,9);
		header("Content-Encoding: gzip");
		header("Vary: Accept-Encoding");
		header("Content-Length: ".strlen($content));
	}
	return $content;
}
?>
