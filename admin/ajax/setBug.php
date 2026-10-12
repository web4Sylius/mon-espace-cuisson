<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/admin/admin_global.php');
require_once($_SERVER['DOCUMENT_ROOT']."/admin/taskmanager/taskManager.class.php");

//Création de la tâche ou du bug selon ce que l'utilisateur à choisi
if(!empty($_POST)){

	$suivi_type        = 'bug';
	$suivi_responsable = 'ariel';

	//Récupération du libellé du bug
	if(isset($_POST['bug_libelle']) && $_POST['bug_libelle'] != ''){
		$suivi_libelle = addslashes($_POST['bug_libelle']);
	}

	//Récupération du contenu de la tâche
	if(isset($_POST['bug_contenu']) && $_POST['bug_contenu'] != ''){
		$suivi_description = addslashes(nl2br($_POST['bug_contenu']));
	}
  
	$suivi_priorite = 2;
	if(isset($_POST['urgent']) && $_POST['urgent'] == 1) $suivi_priorite = 1;

	$tmgr = new TaskManager($DB_site2, '', $_COOKIE['admin_username']);
  
  // test tsi
  //$testArray = $_FILES['task_file'];
  //(!isset($_FILES)) ? $result="Rien" : $result = implode(",",$testArray);
  //'long_desc'     =>  $result,
  
	$aTaskData = array(
		'name'          => $suivi_libelle,
		'long_desc'     => $suivi_description,
		'start_date'    => date('d/m/Y'),
		'excepted_date' => date('d/m/Y'),
		'category'      => 1,
		'in_charge'     => $suivi_responsable,
		'members'       => array($_COOKIE['admin_username']),
		'priority'      => $suivi_priorite,
		'type'          => $suivi_type,
		'duration'      => ''
	);

	if(isset($_POST['remote'])) $aTaskData['remote'] = $_POST['remote'];

	if(!$tmgr->addTask($aTaskData, $_FILES)){
            print implode("\n",$tmgr->errors());
            exit;
        }
  
        echo "creationok";
}