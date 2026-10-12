<?php

$nostat=1;
include_once("host_config_admin.php");
include_once($rootpath."admin/email.php");
require_once($rootpath."admin/taskmanager/taskManager.class.php");

$DB_site2=clone $DB_site2;

$tablehtml = ""
. "<table style=\"width:800px;font-size:12px;border:solid 1px #CCC;\">"
     . "<thead style=\"font-weight:bold;text-align:center;background-color:#CCC;\">"
          . "<tr>"
               . "<td width=\"90\">Date fin</td>"
               . "<td width=\"90\">ID</td>"
               . "<td width=\"100\">Responsable</td>"
               . "<td width=\"100\">Intervenants</td>"
               . "<td>Nom</td>"
               . "<td width=\"90\">Etat</td>"
          . "</tr>"
     . "</thead>";

$statuscolor = array("working"=>"#bdf","new"=>"#fdb","finished"=>"#cfa");
$html = "";

$today=new DateTime();
$listStatus=Task::listStatus();
$settings=array("filter_cat"=>"");
$tm=new TaskManager($DB_site2,$settings,null);
$members=$tm->members();
foreach($members as $member=>$name){
    $settings=array(
    'prefix'	=> 'SI',
    'type'	=> 'suivi',
    "filter_resp"=>$member,"filter_interv"=>$member,"filter_cat"=>null,"tasksearch"=>null);
    $tm=new TaskManager($DB_site2,$settings,$member);
    $tasks=$tm->listTasks();
    
    $table=array();
    
    foreach($tasks as $task){
	$mlist="";
	foreach($task->members() as $m) $mlist.=$members[$m]."<br>";
	$tr="<tr>
	  <td>".$task->exceptedDate()->format("d/m/Y")."</td>
	  <td><a href=\"https://www.ma-cave-a-vin.fr/admin/task.php?a=showTask&task=".$task->id()."\">SI#".$task->id()."</a></td>
	  <td>".$members[$task->inCharge()]."</td>
	  <td>".$mlist."</td>
	  <td>".$task->name()."</td>
	  <td style=\"background-color:".$statuscolor[$task->status()].";\">".$listStatus[$task->status()]."</td>
	</tr>";
	$diff=$today->diff($task->exceptedDate);
	if($diff->invert==1 || $diff->days==0){
	    $tkey=($task->inCharge()==$member?"owner":"member").($diff->days==0?"today":"late");
	    $table[$tkey][]=$tr;
	}
	
    }
    
    $settings=array(
    'prefix'	=> 'SB',
    'type'	=> 'bug',
    "filter_resp"=>$member,"filter_interv"=>$member,"filter_cat"=>null,"tasksearch"=>null);
    $tm=new TaskManager($DB_site2,$settings,$member);
    $tasks=$tm->listTasks();
    
    foreach($tasks as $task){
	$mlist="";
	foreach($task->members() as $m) $mlist.=$members[$m]."<br>";
	$tr="<tr>
	  <td>".$task->exceptedDate()->format("d/m/Y")."</td>
	  <td><a href=\"https://www.ma-cave-a-vin.fr/admin/task.php?a=showTask&task=".$task->id()."\">SB#".$task->id()."</a></td>
	  <td>".$members[$task->inCharge()]."</td>
	  <td>".$mlist."</td>
	  <td>".$task->name()."</td>
	  <td style=\"background-color:".$statuscolor[$task->status()].";\">".$listStatus[$task->status()]."</td>
	</tr>";
	$diff=$today->diff($task->exceptedDate);
	if($diff->invert==1 || $diff->days==0){
	    $tkey=($task->inCharge()==$member?"owner":"member").($diff->days==0?"today":"late");
	    $table[$tkey][]=$tr;
	}
	
    }
    
      if($table!=array()){
	  $html="<div style=\"font-size:12px;\">
	  <p style=\"color:#BB0000;\">Suivi des taches et des bugs pour : ".$name." (en temps que Responsable)</p>";
	  if(is_array($table['ownertoday']))
	    $html.="Taches et Bugs à finaliser pour ce jour :
	    ".$tablehtml
	    .implode("",$table['ownertoday'])."
	    </table><br>";
	    
	  if(is_array($table['ownerlate']))
	    $html.="Taches et bugs en retard :
	    ".$tablehtml
	    .implode("",$table['ownerlate'])."
	    </table>";
	
	  $html.="<p style=\"color:#BB0000;\">Suivi des taches et des bugs pour : ".$name." (en temps que Intervenant)</p>";
	  if(is_array($table['membertoday']))
	    $html.=" Taches et Bugs à finaliser pour ce jour :
	    ".$tablehtml
	    .implode("",$table['membertoday'])."
	    </table><br>";
	    
	  if(is_array($table['memberlate']))
	    $html.="Taches et bugs en retard :
	    ".$tablehtml
	    .implode("",$table['memberlate'])."
	    </table>
	    </div>";
      
	  $destinataire=$DB_site2->query_first("SELECT mailFR FROM admin_contact WHERE username='".$member."'");
	  if(!isset($_GET['test'])){
	      if($destinataire['mailFR']!=""){
		  $mail=new email();
		  $mail->expediteur="contact@mon-espace-cuisson.com";
		  $mail->expname="Ma Cave à Vin";
		  $mail->destinataire=$destinataire['mailFR'];
		  $mail->to=array("pr@mon-espace-cuisson.com");
		  $mail->subject="Suivi des taches et des bugs Ma Cave à Vin";
		  $mail->body=$html;
		  $mail->sendmail();
	      }
	  }else{
	      print "<hr>".$destinataire['mailFR'];
	      print $html;
	  }
      }
}






?>