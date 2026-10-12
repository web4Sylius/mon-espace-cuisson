<?php
require_once($_SERVER['DOCUMENT_ROOT']."/admin/admin_global.php");
require_once($_SERVER['DOCUMENT_ROOT']."/admin/taskmanager/taskManager.class.php");
require_once($_SERVER['DOCUMENT_ROOT']."/admin/taskmanager/sendTask.class.php");

//Création de la tâche ou du bug selon ce que l'utilisateur à choisi
if(!empty($_POST)){

	//Gestion du type de suivi
	$suivi_type = 'suivi';

	//Gestion du responsable
	if(isset($_POST['demande_responsable']) && is_string($_POST['demande_responsable'])){
		$suivi_responsable = $_POST['demande_responsable'];
	}

	//Récupération du libellé de la tâche
	if(isset($_POST['task_libelle']) && $_POST['task_libelle'] != ''){
		$suivi_libelle = addslashes($_POST['task_libelle']);
	}

	//Récupération du contenu de la tâche
	if(isset($_POST['task_contenu']) && $_POST['task_contenu'] != ''){
		$suivi_description = addslashes(nl2br($_POST['task_contenu']));
	}
		
	//Associé à
	if(isset($_POST['task_link']) && is_string($_POST['task_link'])){
		$suivi_link = $_POST['task_link'];
	}
	
	//Associé à (Type)
	if(isset($_POST['task_type']) && is_string($_POST['task_type'])){
		$suivi_linkType = $_POST['task_type'];
	}

	$tmgr = new TaskManager($DB_site2, '', $_COOKIE['admin_username']);

	$aTaskData = array(
		'name'          => $suivi_libelle,
		'long_desc'     => $suivi_description,
		'start_date'    => date('d/m/Y'),
		'excepted_date' => $_POST['task_date'],
		'category'      => 1,
		'in_charge'     => $suivi_responsable,
		'members'       => $_POST['intervenant'],
		'link'          => $suivi_link,
		'link_type'     => $suivi_linkType,
		'priority'      => 2, //Priorité haute par défaut
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