<?php
error_reporting(E_ALL ^ E_NOTICE);

include_once "../../configurations/www.ma-cave-a-vin.fr/config.php";
include_once "../../class/mysql.php";

$DB_site2 = new DB_Sql;
$DB_site2->database	= $DBname;
$DB_site2->server = $db_server;
$DB_site2->user = $DBusername;
$DB_site2->password	= $DBpassword;
$DB_site2->connect();
$DBpassword = "";
$DB_site2->password	= "";

include_once("taskManager.class.php");
include_once("task.class.php");
include_once("taskLog.class.php");

$trusted=array("88.174.152.207","37.187.136.179","88.184.216.175","94.23.211.169");

$tm = new TaskManager($DB_site2,false,false);
$rcv = new rcv_task($DB_site2,$trusted);

switch($_POST['action']) {
    case "test":
		$rcv->test();
		break;
    case "add":
		$rcv->add_task($_POST['task'],$_POST['from']);
		break;
    case "log":
		$rcv->get_log($_POST['taskid']);
		break;
    case "users":
		$rcv->users();
		break;
    default:
		$rcv->noaction();
}

class rcv_task {
	private $DB;
	private $trusted;

	function __construct($DB,$trusted) {
		$this->DB=$DB;
		$this->trusted=$trusted;
	}

	function test() {
		$this->verif();
		$this->xml(array("result"=>"OK"));
	}

	function noaction(){
		$this->verif();
		$this->xml(array("result"=>"error","error"=>"noaction"));
	}

	function add_task($stask,$from) {
		$this->verif();
		$task=unserialize(base64_decode($stask));
		$ntask=new task($this->DB,false);
		$ntask->setName($task->name());
		//$ntask->setShortDesc($task->shortDesc());
		$ntask->setLongDesc($task->longDesc());
		$ntask->setStartDate($task->startDate()->format("d/m/Y"));
		$ntask->setInitialDate($task->initialDate()->format("d/m/Y"));
		$ntask->setExceptedDate($task->exceptedDate()->format("d/m/Y"));
		$categorylist=$ntask->listCategory();
		if(!array_key_exists($task->category(),$categorylist))
			$ntask->setCategory("gen");
		else
			$ntask->setCategory($task->category());
		$ntask->setOwner($task->owner());
		$ntask->setInCharge($task->inCharge());
		$ntask->setMembers($task->members());
		$ntask->setStatus($task->status());
		$ntask->setPriority($task->priority());
      	$ntask->setPriority_name($task->priority_name());
		$ntask->setType($task->type());
		$remote[$from]=$task->id();
		$ntask->setRemote($remote);

		$id=$ntask->save();
		if(!$id) 
			$data=array("result"=>"error");
		else{
			$data=array("result"=>"OK","taskid"=>$id);
			$log=new TaskLog($this->DB,$id,$task->owner());
			$log->add("Création tâche depuis ".$from);
		}
		$this->xml($data);
	}

	function get_log($taskid) {
		$this->verif();
		$log=new TaskLog($this->DB,$taskid,null);
		$data["result"]="OK";
		$data["log"]=serialize($log->msglist());
		$this->xml($data);
	}

	function users() {
		$this->verif();
		$tm=new TaskManager($this->DB,null,null);
		$data['result']="OK";
		foreach($tm->members() as $l=>$member) {
			$data['member'][]=$l;
		}
		$this->xml($data);
	}

	function verif() {
		if(in_array($_SERVER['REMOTE_ADDR'],$this->trusted))
			return true;
		else
			$this->xml(array("result"=>"error","error"=>"unauthorized"));
	}

	function xml($data) {
		header ("Content-Type:text/xml; charset=ISO-8859-15");
		
		print "<?xml version=\"1.0\" encoding=\"ISO-8859-15\"?>
		<xml>";
		foreach($data as $key=>$value)
			if(is_array($value))
				foreach($value as $v) print "<$key>$v</$key>";
			else
				print "<$key>$value</$key>";
		print "</xml>";
		
		exit;
	}
}
?>