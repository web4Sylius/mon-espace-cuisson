<?php
require_once($_SERVER['DOCUMENT_ROOT']."/admin/admin_global.php");
require_once(BACK_PATH."/taskmanager/taskManager.class.php");
require_once(BACK_PATH."/taskmanager/taskTemplate.class.php");

$superusers = array("philippe", "cc", "benoit","ariel","jessica","florence","jbandre");

$menuParams = array('filter_cat','show_closes','filter_interv','filter_resp','filter_own','tasksearch','filter_status','filter_types','filter_priorite', 'tasksearch', 'tri_type', 'tri_order');

foreach ($menuParams as $param) $params[$param] = $_GET[$param];
//Statut de l'utilisateur en cours
$is_superuser = in_array($user_info['username'], $superusers);

if(isset($_GET['owner']) && $_GET['owner'] == 1)
{
	//Supprimer les lignes ci-dessous si on veut pouvoir filtrer sur l'onglet "Tâches/Bugs demandées"
	$params['filter_cat']      = '';
	$params['filter_interv']   = '';
	$params['filter_resp']     = '';
	$params['filter_own']      = '';
	$params['tasksearch']      = '';
	$params['filter_status']   = '';
	$params['filter_types']    = '';
	$params['filter_priorite'] = '';
	$params['tasksearch']      = '';
	$params['filter_own']      = $user_info['username'];
	$params['restrict_resp']   = 1;
} 
else
{
	//Utilisateur classique, on pré-filtre ses propres tâches/bugs
	if(!$is_superuser)
	{
		$params['filter_resp']   = $user_info['username'];
		$params['filter_interv'] = $user_info['username'];
		$params['filter_own']    = $user_info['username'];

	}

	$params['restrict_resp'] = 0;
}

$tmgr = new TaskManager($DB_site2, $params, $user_info['username']);
$tpl  = new TaskTemplate($tmgr);

if(isset($_GET['owner']) && $_GET['owner'] == 1) $tpl->type_suivi = 'owner';

echo $tpl->generate_liste();