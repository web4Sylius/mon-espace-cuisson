<?php
require_once($_SERVER['DOCUMENT_ROOT']."/admin/admin_global.php");
require_once($_SERVER['DOCUMENT_ROOT']."/admin/taskmanager/taskManager.class.php");
require_once($_SERVER['DOCUMENT_ROOT']."/admin/taskmanager/taskTemplate.class.php");
require_once($_SERVER['DOCUMENT_ROOT']."/admin/taskmanager/sendTask.class.php");

include_once($_SERVER['DOCUMENT_ROOT']."/admin/header.php");
include_once($_SERVER['DOCUMENT_ROOT']."/admin/menu.php");

setlocale(LC_TIME, "fr_FR.utf8");

//Utilisateurs ayant visibilité sur toutes les tâches
$superusers = array("philippe", "cc", "benoit","ariel","jessica","florence","jbandre");
ini_set('display_errors', 0);

$params_url = '';
foreach($_GET as $key => $value){
     if($params_url != '') $params_url .= ',';
     $params_url .= $key . ": " . (is_string($value) ? "'" . $value . "'" : $value);
}

?>
<script language="javascript" src="../javascripts/prototype.js"></script>
<link rel="stylesheet" href="taskmanager/css/smoothness/jquery-ui-1.10.3.custom.min.css" />
<script src="taskmanager/scripts/jquery-2.0.3.min.js"></script>
<script src="taskmanager/scripts/jquery.ui.datepicker-fr.js"></script>
<script src="taskmanager/scripts/jquery-ui-1.10.3.custom.min.js"></script>
<script src="taskmanager/scripts/tasklog.js"></script>
<script src="calendrier.js" language="javascript"></script>
<link rel="stylesheet" href="cal.css" />
<script src="filebox.js"></script>
<link rel="stylesheet" href="filebox.css" />
<script type="text/javascript">
     function load_liste(tri_type = '', tri_order = '')
     {
          $jQuery( "#tasklist" ).html( "<img src=\"images/loader.gif\" style=\"margin:0 auto; width: 50px; margin: 25px 0 25px 0;\">" );

          if(tri_type == '') tri_type = 'type';
          if(tri_order == '') tri_order = 'desc';

          $jQuery.ajax({
               method : "GET",
               url    : "taskmanager/ajax/liste_taches.php",
               data   : { owner: 0, tri_type : tri_type, tri_order : tri_order, <?php echo $params_url; ?> }
          })
          .done(function( msg ) {
               $jQuery( "#tasklist" ).html( msg );
          }); 
     }

     function load_liste_owner(tri_type = '', tri_order = '')
     {
          $jQuery( "#taskownlist" ).html( "<img src=\"images/loader.gif\" style=\"margin:0 auto; width: 50px; margin: 25px 0 25px 0;\">" );

          if(tri_type == '') tri_type = 'type';
          if(tri_order == '') tri_order = 'desc';

          $jQuery.ajax({
               method : "GET",
               url    : "taskmanager/ajax/liste_taches.php",
               data   : { owner: 1, tri_type : tri_type, tri_order : tri_order, <?php echo $params_url; ?> }
          })
          .done(function( msg ) {
               $jQuery( "#taskownlist" ).html( msg );
          }); 
     }
     //affichage de l'onglet pour les taches des stagiaires visibles pour leur tuteur
     function load_liste_student(tri_type = 'datefin', tri_order = 'asc')
     {
          $jQuery( "#taskstudentlist" ).html( "<img src=\"images/loader.gif\" style=\"margin:0 auto; width: 50px; margin: 25px 0 25px 0;\">" );

          if(tri_type == '') tri_type = 'type';
          if(tri_order == '') tri_order = 'desc';
          var student = 1;
          $jQuery.ajax({
               method : "GET",
               url    : "taskmanager/ajax/liste_taches.php",
               data   : { owner: 0, tri_type : tri_type, tri_order : tri_order ,student : student,<?php echo $params_url; ?> }
          })
          .done(function( msg ) {
               $jQuery( "#taskstudentlist" ).html( msg );
          }); 
     }

     $jQuery(document).ready(function(){ load_liste(); load_liste_owner(); });
</script>
<h1>Suivi des tâches en cours</h1>
<?php

if($user_info['username'] == 'nia' || $user_info['username'] == 'benoit' || $user_info['username'] == 'ariel' || $user_info['username'] == 'cc' || $user_info['username'] == 'philippe'){
	echo "<a href=\"planning_task.php\">accéder au planning</a>";
}
if(isset($_GET['task']) && !empty($_GET['task'])) $params['task_id'] = $_GET['task'];

$menuParams = array('filter_cat','show_closes','filter_interv','filter_resp','filter_own','tasksearch','filter_status','filter_types','filter_priorite', 'tasksearch');
//$menuParams = array('filter_cat','show_closes','filter_member','tasksearch','filter_status', 'type');
foreach ($menuParams as $param) $params[$param] = $_GET[$param];

//Statut de l'utilisateur en cours
$is_superuser = in_array($user_info['username'], $superusers);

//Utilisateur classique, on pré-filtre ses propres tâches/bugs
if(!$is_superuser){
     $params['filter_resp'] = $user_info['username'];
     $params['filter_interv'] = $user_info['username'];
}
    
//URLs avec lesquelles pour réaliser des partages de tâches
$remotes = array("Mon Espace Cuisson" => "http://www.mon-espace-cuisson.com/Aci-AdM/");
$localsite = "Ma Cave a Vin";

$tmgr = new TaskManager($DB_site2, $params, $user_info['username']);
$tpl  = new TaskTemplate($tmgr);

echo $tpl->css();
echo $tpl->menu($is_superuser);
echo $tpl->location($_GET['a'], $params);

//TODO: améliorer la gestion des actions
switch($_GET['a']) {
     
     case 'addTask':

          if(empty($_POST) || !$tmgr->addTask($_POST)){
               $content = $tpl->formAddTask($_POST);
          } else {
               $content = $tpl->tasks();
          }
          
          break;
          
     case 'addLog':
          
          if(!empty($_POST)) $tmgr->addLog($_GET['task'], $_POST);
          // pas de break car on affiche la tâche après l'ajout du suivi
          
     case 'showTask':
          
          if (!$content = $tpl->showTask($_GET['task'])) $content = $tpl->tasks();
          
          break;
          
     case 'editTask':
    
          if (!empty($_POST) && $tmgr->editTask($_GET['task'], $_POST)) {
               
               $content = $tpl->showTask($_GET['task']);
               
          } elseif (!$content = $tpl->formEditTask($_GET['task'])){
               
               $content = $tpl->tasks();
          }
          
          break;
          
     default:
          $content = $tpl->tasks($user_info['username']);
}

echo $tpl->notif('errors');
echo $tpl->notif('success');
echo $content;

echo $tpl->js();

include "./footer.php";