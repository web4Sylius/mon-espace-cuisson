<?php
require_once(BACK_PATH."/taskmanager/taskManager.class.php");

class TaskTemplate {
     
     private $tm;
     private $js;
     private $suiviicon = array(
                              "tel_out" => "<img src=\"images/tel_out.png\"><br>",
                              "tel_in"  => "<img src=\"images/tel_in.png\"><br>",
                              "mail_in" => "<img src=\"images/get_mail.png\"><br>"
                         );

     public $taskId;

     public $type_suivi = 'classic';

     function __construct($tm){
          
          //Vérifie si $tm est une instance de la classe Taskmanager
          if(!is_a($tm, 'TaskManager')) throw new Exception('TaskManager attendu');
            
          $this->tm = $tm;
          $this->js = "var \$jq = jQuery.noConflict();";
     }

     function css(){
          
          return ('<style>'.file_get_contents('taskmanager/css/style.css').'</style>');
     }
     
     function js() {
          
          return ('<script>'.$this->js.'</script>');
     }

     function pushJs($file) {
          
          if (!in_array($file, array('taskadd', 'menu', 'status')) || !($js = file_get_contents('taskmanager/scripts/'.$file.'.js'))) return (false);
         
          $this->js .= $js;
          return (true);
     }

     function filebox(){
          
          $this->js .= ""
          . "\nfb=new filebox('fb');"
          . "fb.dataurl='taskmanager/fichiers.php?taskid=".$this->taskId."&amp;parent=0';"
          . "mail_pagepj('fb.showselect',['undefined',mail_addpjs]);";
     }

     function location($action, $params = array()){
          
          $tabTask = array();
          if(isset($params['task_id']) && is_numeric($params['task_id'])) $tabTask = $this->tm->getTask($params['task_id']);

          $prefix   = '';
          $suivi_id = '';
          if(isset($tabTask->type) && !empty($tabTask->type)){
               switch($tabTask->type){
                    case 'bug':
                         $prefix   = 'SB#';
                         $suivi_id = $tabTask->id;
                         break;
                    case 'suivi':
                         $prefix   = 'SI#';
                         $suivi_id = $tabTask->id;
                         break;
               }
          }
          
          $html	= ''
          . '<div class="tasklocation">'
               . '<ul>'
                    . '<li><a href="?a=taskList">Liste des suivis</a></li>';
          
          switch ($action) {
               case 'addTask':

                     $page = 'Nouveau suivi';

                     break ;
               case 'showTask':

                     $page = 'Suivi '.$prefix.$suivi_id;

                 break ;
               case 'editTask':

                     $page = 'Édition du suivi '.$prefix.$suivi_id;

                     break ;
               case 'addCat':

                     $page = 'Ajout d\'une catégorie';

                     break ;
               default:

                     $page = null;
          }
                    
          if (!is_null($page)) $html .= '<li>'.$page.'</li>';
            
          $html .= ''
               . '</ul>'
          . '</div>'
          . '<div class="clear">'
          . '</div>';
                    
                    return ($html);
     }

     function menu($is_superuser = false) {
          
          $showClosedTasks = $this->tm->taskSettings('show_closes');
          
          //Vérifie si "Afficher les suivis clos" est coché, afin de recocher au chargement
          $checked = (($showClosedTasks == 'on') ? 'checked = "checked"' : null);
          
          //Liste des catégories de suivi
          $listCategory	= Task::listCategory();
          
          //Liste déroulante catégories
          array_unshift($listCategory, '-- Toutes --');
          $listCategory['new_cat'] = 'Nouvelle catégorie...';
          $catSelect = $this->select(
               'menucat',
               'filter_cat',
               $listCategory,
               $this->tm->taskSettings('filter_cat')
          );
    
          $listStatus = Task::listStatus();
          
          //Liste déroulante statuts
          array_unshift($listStatus, '-- Tous --');
          $statusSelect = $this->select(
               'menustatus',
               'filter_status',
               $listStatus,
               $this->tm->taskSettings('filter_status')
          );
          
          $listTypes = Task::listTypes();
          
          //Liste déroulante types de suivis
          array_unshift($listTypes, '-- Tous --');
          $typesSelect = $this->select(
               'menutypes',
               'filter_types',
               $listTypes,
               $this->tm->taskSettings('filter_types')
          );
          
          $listPriorite = Task::listPriorites();
          
          //Liste déroulante priorité
          array_unshift($listPriorite, '-- Toutes --');
          $prioriteSelect = $this->select(
               'menupriorite',
               'filter_priorite',
               $listPriorite,
               $this->tm->taskSettings('filter_priorite')
          );
          
          //Liste déroulantes membres, accessible uniquement aux superusers
          $listMembers	= $this->tm->members();
          array_unshift($listMembers, '-- Tous --');
          if($is_superuser){
               
               $respSelect   = $this->select(
                    'menuresp',
                    'filter_resp',
                    $listMembers,
                    $this->tm->taskSettings('filter_resp')
               );

               $intervSelect   = $this->select(
                    'menuinterv',
                    'filter_interv',
                    $listMembers,
                    $this->tm->taskSettings('filter_interv')
               );

               $ownSelect   = $this->select(
                    'menuown',
                    'filter_own',
                    $listMembers,
                    $this->tm->taskSettings('filter_own')
               );
          }        
          
          //Recherche manuelle
          $tasksearch=$this->tm->taskSettings("tasksearch");
          $this->pushJs('menu');

          $return = ""
          . "<div id=\"formurecherche\" class=\"commande_detail\">"
               . "<div style=\"padding: 10px;\">"
                    . "<a title=\"Nouveau client\" href=\"?a=addTask\" class=\"nouveau_client\"><img alt=\"Nouveau client\" src=\"images/icon/edit_add_32.png\" class=\"icon\" style=\"margin: 0 5px 0 0; padding: 0;\">Nouveau suivi</a>"
                    . "<form id=\"taskmenusearch\" method=\"get\" style=\"margin: 0;\">"
                         . "<strong>Rechercher :</strong> <input type=\"text\" name=\"tasksearch\" value=\"".$tasksearch."\" /> <input type=\"submit\" title=\"Rechercher\" alt=\"Rechercher\" value=\"\" class=\"loupe\">"
                    . "</form>"
                    . "<form id=\"taskmenuaction\" method=\"get\" style=\"margin: 5px 0;\">"
                         . "<strong>Catégorie :</strong> ".$catSelect
                         . "<div class=\"separateur_vertical\"></div>"
                         . "<strong>Statut :</strong> ".$statusSelect
                         . "<div class=\"separateur_vertical\"></div>"
                         . "<strong>Type de suivi :</strong> ".$typesSelect
                         . "<div class=\"separateur_vertical\"></div>"
                         . "<strong>Priorité :</strong> ".$prioriteSelect
                         . "<br>";
          if(isset($respSelect) && isset($intervSelect)){               
          $return .= ""
                         . "<strong>Responsable :</strong> ".$respSelect
                         . "<div class=\"separateur_vertical\"></div>"
                         . "<strong>Intervenant :</strong> ".$intervSelect;
          }
          $return .= ""
                         . "<input type=\"checkbox\" ".$checked." id=\"taskshowcloses\" name=\"show_closes\" style=\"vertical-align: -2px; margin-top: 10px;\" /> afficher les suivis clos ou à clôturer"
                    . "</form>"
               . "</div>"
          . "</div>"
          /*. "<div class=\"taskmenu\">"
               . "<form id=\"taskmenusearch\" method=\"get\">"
                    . "<input type=\"text\" name=\"tasksearch\" value=\"".$tasksearch."\" />"
                    . "<input type=\"hidden\" name=\"".$type."\" />"
                    . "<input type=\"submit\" value=\"Rechercher\" />"
               . "</form>"
               . "<a href=\"?a=addTask&amp;".$type."\">Ajouter un ".$type."</a>"
               . "<form id=\"taskmenuaction\" method=\"get\">"
                    . "<input type=\"hidden\" name=\"$type\" value=\"1\" />"
                    . $catSelect
                    . $statusSelect
                    . $memberSelect
                    //. "<input type=\"button\" name=\"closeTask\" value=\"Clôturer le(s) ".$type."(s) sélectionné(s)\" />"
                    . "<input type=\"checkbox\" ".$checked." id=\"taskshowcloses\" name=\"show_closes\" />"
                    . "<label for=\"taskshowcloses\">afficher les ".$type."s clos</label>"
               . "</form>"
          . "</div>"*/
          . "<div class=\"clear\"></div>";
          
          return $return;
     }

  function notif($type) {
    if (!in_array($type, array('errors', 'success')))
      return (false);
    $notifs = $this->tm->$type();
    if (empty($notifs))
      return (null);
    $html = '<ul id="task'.$type.'" class="notif">';
    foreach ($notifs as $notif) {
      $html .= '<li>'.$notif.'</li>';
    }
    $html .= '</ul>';
    return ($html);
  }

     function formAddTask($formdata) {
          
          $members	   = $this->tm->members();
          $priorities	   = $this->tm->priorities();
          $defaultInCharge = (isset($formdata['in_charge'])?$formdata['in_charge']:$this->tm->user());
          
          $inChargeSelect = $this->select(
               'taskincharge',
               'in_charge',
               $members,
               $defaultInCharge
          );
          
          $selectType = $this->select(
               'tasktype',
               'type',
               array('suivi' => 'Tâche', 'bug' => 'Bug')
          );
          
          $priority = $this->select(
               'priority',
               'priority',
               $priorities
          );
          
          array_unshift($members, 'Ajouter...');
          $membersSelect = $this->select('taskmbrs', 'membersList', $members);
          
          $membersSelectMult = $this->select(
               'taskmbrs', 
               'members', 
               $members, 
               $formdata['members'], 
               true
          );
          
          /*$catSelect = $this->select(
               'taskcat', 
               'category', 
               Task::listCategory(),
               $formdata['category']
          );*/
          
          $inputCategorie = $this->input('taskcat', 'category', 1, true);

          $date = date('d/m/Y');

          $dateSelect = "<div id=\"tski\"><label for=\"tasksdate\">Date d'échéance : </label></div>";

          $shortDateSelect = "<div id=\"tskb\"><label for=\"taskedate\">Date de fin&nbsp;: </label>";
          $shortDateSelect .= $this->select(
                                   'taskshortdate',
                                   'short_date',
                                   array(
                                        'Sous 1 heure',
                                        'Sous 4 heures',
                                        'Sous 1 jour',
                                        'Autre',
                                   ),
                                   0
                              );
          $shortDateSelect .="</div>";
      
          if (!empty($formdata['members'])){
               
               foreach($formdata['members'] as $member){
                    
                    $listMembers .= ''
                    . '<li id="mbr_'.$member.'">'
                         . '<p>'.(array_key_exists($member, $members) ?  $members[$member] : $member).'</p>'
                         . '<span class="ui-icon ui-icon-trash">'
                    . '</li>';
               }
          }
	  
          $this->pushJs('taskadd');

          $selectremote=$this->chooseRemotes();
    
          $return = ""
          . "<div id=\"taskadd\">"
               . "<form method=\"post\">"
                    . "<label for=\"tasksdate\">Type de suivi : </label>"
                    . $selectType
                    . "<br />"
                    . $dateSelect
                    . $shortDateSelect
                    . "<input type=\"text\" class=\"date\" id=\"taskedate\" size=\"10\" maxlength=\"10\" name=\"excepted_date\" value=\"".$date."\" /><br />"
                    . "<label for=\"duration\">Durée estimée :</label>"
                    . "<input type=\"text\" name=\"duration\" size=\"2\" value=\"".$duration."\"> heure(s)"
                    . "<br />"
                    . "<label for=\"duration_real\">Durée réelle :</label>"
                    . "<input type=\"text\" name=\"duration_real\" size=\"2\" value=\"".$duration_real."\"> heure(s)"
                    . "<br />"
                    . "<label for=\"priority\">Priorité : </label>"
                    . $priority
                    . "<br />"
                    /*. "<label for=\"tasksd\">Descriptif court&nbsp;:</label>"
                    . "<input type=\"text\" id=\"tasksd\" name=\"short_desc\" value=\"".$formdata['short_desc']."\" />"
                    . "<br />"*/
                    . "<label for=\"taskname\">Intitulé : </label>"
                    . "<input type=\"text\" id=\"taskname\" name=\"name\" value=\"".$formdata['name']."\" style=\"width: 700px;\" />"
                    . "<br />"
                    . "<label for=\"taskcd\">Descriptif complet&nbsp;: </label>"
                    . "<div id=\"editor\">"
                         . "<textarea id=\"taskcd\" name=\"long_desc\" class=\"ckeditor\">".$formdata['long_desc']."</textarea>"
                    . "</div><div class=\"clear\"></div>"
                    /*. "<label for=\"taskcat\">Catégorie&nbsp;:</label>"
                    . $catSelect
                    . "<br />"*/
                    . "<label for=\"taskincharge\">Responsable&nbsp;:</label>"
                    . $inChargeSelect
                    . "<br />"
                    . "<label for=\"taskmbrs\">Intervenants&nbsp;:</label>"
                    . "<div id=\"taskhidden\">"
                         . $membersSelect
                         . " <span style=\"font-size:11px;\">(Tâches uniquement)</span>"
                         . "<ul id=\"taskmbrslist\">$listMembers</ul>"
                    . "</div>"
                    . "<noscript>$membersSelectMult</noscript>"
                    . "<br />"
                    . "<label for=\"tasklinklabel\">Associer a&nbsp;:</label>"
                    . "<input type=\"text\" id=\"tasklinklabel\" value=\"".$formdata['link']."\" />"
                    . "<span  id=\"tasklinkshowtype\">".$formdata['link_type']."</span>"
                    . "<input type=\"hidden\" id=\"tasklinkid\" name=\"link\" value=\"".$formdata['link']."\" />"
                    . "<input type=\"hidden\" id=\"tasklinktype\" name=\"link_type\" value=\"".$formdata['link_type']."\" />"
                    . "<input type=\"hidden\" class=\"date\" id=\"tasksdate\" size=\"10\" maxlength=\"10\" name=\"start_date\" value=\"".$date."\" />"
                    . $inputCategorie
                    . $selectremote
                    . "<br />"
                    . "<input type=\"submit\" value=\"Ajouter le suivi\" />"
               . "</form>"
          . "</div>";

          return $return;
     }

     function formEditTask($taskid){
          
          $task = $this->tm->getTask($taskid);

          if (!$task) return (false);
          
          $type	  = $task->type();
          $members    = $this->tm->members();
          $priorities = $this->tm->priorities();

          $name   	= $task->name();
          $exceptedDate	= $task->exceptedDate()->format('d/m/Y');
          //$shortDesc	= htmlentities($task->shortDesc(),ENT_COMPAT,"ISO-8859-15");
          $longDesc      = $task->longDesc();
          $duration = $task->duration();
          $duration_real = $task->duration_real();
          
          $priority	= $this->select(
               'priority',
               'priority',
               $priorities,
               $task->priority()
          );
    
          $inChargeSelect = $this->select(
               'taskincharge',
               'in_charge',
               $members,
               $task->inCharge()
          );
          
          array_unshift($members, 'Ajouter...');
          $membersSelect     = $this->select('taskmbrs', 'membersList', $members);
          $membersSelectMult = $this->select('taskmbrs', 'members', $members, null, true);

          $link     = $task->link();
          $linkType = $task->linkType();
          
          $listTypes = $task->listTypes();
          
          $listStatus = $task->listStatus();
          if (!$this->tm->checkPerm($task, 'closeTask', $this->user)) unset($listStatus['closed']);
          
          $typeSelect = $this->select(
               'tasktype', 'type', $listTypes, $task->type()
          );
          
          $statusSelect = $this->select(
               'taskstatus', 'status', $listStatus, $task->status()
          );

          //$catSelect = $this->select('taskcat', 'category', $task->listCategory(), $task->category());

          if ($type == 'bug'){
               $shortDateSelect = $this->select(
                    'taskshortdate',
                    'short_date',
                    array(
                         'Sous 1 heure',
                         'Sous 4 heures',
                         'Sous 1 jour',
                         'Autre',
                    )
               );
          } else {
               $shortDateSelect = null;
          }

          $taskMembers = $task->members($task);
          if (!empty($taskMembers)){
               foreach($taskMembers as $member){
                    $listMembers .= ''
                    . '<li id="mbr_'.$member.'">'
                         . '<p>'.(array_key_exists($member, $members) ? $members[$member] : $member).'</p>'
                         . '<span class="ui-icon ui-icon-trash">'
                    . '</li>';
               }
          }

          $selectremote=$this->chooseRemotes($task);

          $this->pushJs('taskadd');

          $inputCategorie = $this->input('taskcat', 'category', 1, true);
		  
          $return = ""
          . "<div id=\"taskadd\">"
               . "<form method=\"post\">"
                    . "<p>"
                         . "<label for=\"\">Type de suivi :</label>"
                         . $typeSelect
                    . "</p>"
                    . "<p>"
                         . "<label for=\"\">État :</label>"
                         . $statusSelect
                    . "</p>"
                    . "<p>"
                         . "<label for=\"taskname\">Date d'échéance :</label>"
                         . $shortDateSelect
                         . "<input type=\"text\" class=\"date\" id=\"taskedate\" size=\"10\" maxlength=\"10\" name=\"excepted_date\" value=\"".$exceptedDate."\" />"
                    . "</p>"
                    . "<p>"
                         . "<label for=\"duration\">Durée estimée :</label>"
                         . "<input type=\"text\" name=\"duration\" size=\"2\" value=\"".$duration."\"> heure(s)"
                    . "</p>"
                    . "<p>"
                         . "<label for=\"duration_real\">Durée réelle :</label>"
                         . "<input type=\"text\" name=\"duration_real\" size=\"2\" value=\"".$duration_real."\"> heure(s)"
                    . "</p>"
                    . "<p>"
                         . "<label for=\"priority\">Priorité :</label>"
                         . $priority
                    . "</p>"
                    . "<p>"
                         . "<label for=\"taskname\">Intitulé :</label>"
                         . "<input type=\"text\" id=\"taskname\" name=\"name\" value=\"".$name."\" style=\"width: 700px;\" />"
                    . "</p>"
                    /*. "<p>"
                         . "<label for=\"tasksd\">Descriptif court&nbsp;:</label>"
                         . "<input type=\"text\" id=\"tasksd\" name=\"short_desc\" value=\"".$shortDesc."\" />"
                    . "</p>"*/
                    . "<p>"
                         . "<label for=\"taskcd\">Descriptif complet&nbsp;:</label>"
                         . "<div id=\"editor\">"
                              . "<textarea id=\"taskcd\" name=\"long_desc\" class=\"ckeditor\">".$longDesc."</textarea>"
                         . "</div>"
                         . "<div class=\"clear\"></div>"
                    . "</p>"
                    /*. "<p>"
                         . "<label for=\"taskcat\">Catégorie&nbsp;:</label>"
                         . $catSelect
                    . "</p>"*/
                    . "<p>"
                         . "<label for=\"taskincharge\">Responsable&nbsp;:</label>"
                         . $inChargeSelect
                    . "</p>"
                    . "<label for=\"taskmbrs\">Intervenants&nbsp;:</label>"
                    . "<div id=\"taskhidden\">"
                         . $membersSelect
                         . "<ul id=\"taskmbrslist\">"
                         . $listMembers
                         . "</ul>"
                    . "</div>"
                    . "<noscript>".$membersSelectMult."</noscript>"
                    . "<label for=\"tasklinklabel\">Associer a&nbsp;:</label>"
                    . $inputCategorie
                    . "<input type=\"text\" id=\"tasklinklabel\" value=\"".$link."\" />"
                    . "<span  id=\"tasklinkshowtype\">".$linkType."</span>"
                    . "<input type=\"hidden\" id=\"tasklinkid\" name=\"link\" value=\"".$link."\" />"
                    . "<input type=\"hidden\" id=\"tasklinktype\" name=\"link_type\" value=\"".$linkType."\" />"
                    . "<br />"
                    . $selectremote
                    . "<p>"
                         . "<input type=\"submit\" value=\"Modifier le ".$type."\" />"
                    . "</p>"
               . "</form>"
          . "</div>";
    
          return $return;
     }

  function formAddCat() {
    return <<<HTML
	<form method="post">
	  <p>
        <label for="tasknewcat">Nouvelle catégorie&nbsp;:</label>
        <input type="text" id="tasknewcat" name="new_cat" />
        <input type="submit" value="Créer" />
	  </p>
	</form>
HTML;
  }

     function tasks($username = null) 
     {
          $return = ""
          . "<div id=\"onglet_task\" onclick=\"activeOnglet('tasklist');\">Tâches(".$this->Nb_tache().")/Bugs à réaliser(".$this->Nb_bug().")</div>"
          . "<div id=\"onglet_task_own\" onclick=\"activeOnglet('taskownlist');\">Tâches/Bugs demandées</div>";

          $return .= "<div id=\"tasklist\" style=\"width:100%; text-align: center;\"></div>"
          . "<div id=\"taskownlist\" style=\"width:100%; text-align: center; display: none;\"></div>";
          
          return $return;
     }
     function Nb_bug(){
          $nb = 0;
          $listTasks = $this->tm->listTasks();
          foreach($listTasks as $task){
               if($task->type() == 'bug'){
                   $nb++;  
              }
          }
          return $nb;
     }
     function Nb_tache(){
          $nb = 0;
          $listTasks = $this->tm->listTasks();
          foreach($listTasks as $task){
               if($task->type() == 'suivi'){
                   $nb++;  
              }
          }
          return $nb;
     }
     function generate_liste() 
     {
          $listTasks = $this->tm->listTasks();
          $listStatus   = Task::listStatus();
          $listCategory = Task::listCategory();

          $members = $this->tm->members(false);

          $totalduration = 0;

          if (empty($listTasks)) return '<p class="notask">Aucun suivi ne correspond à votre recherche</p>';

          foreach($listTasks as $task){

               $link     = '?a=showTask&amp;task='.$task->id();
               $taskId   = $this->taskid($task->id());
               $lnkTitle = 'Voir le suivi '.$taskId;
               $inCharge = $task->inCharge();
               $owner    = $task->owner();

               if (array_key_exists($inCharge, $members))
               {
                    $aInCharge = explode(' ',$members[$inCharge]);
                    $inCharge  = $aInCharge[0] . ' ' . substr($aInCharge[1],0,1) . '.';
               } 
               if (array_key_exists($owner, $members))
               {
                    $aOwner = explode(' ',$members[$owner]);
                    $owner  = $aOwner[0] . ' ' . substr($aOwner[1],0,1) . '.';
               } 

               $listMembers = $task->members();
               $taskMembers = "";

               //if($task->status() == 'planned' || $task->status() == 'working') $totalduration += $task->duration();
				if(!in_array($task->status(),array('closed','finished'))) $totalduration += $task->duration();
				
               foreach($listMembers as $member)
               {
                    $aMember = explode(' ',$members[$member]);
                    $taskMembers .= "<li>" . $aMember[0] . ' ' . substr($aMember[1],0,1) . '.' . "</li>";
               } 

               //$taskMembers = (
               //(!empty($listMembers))?' <p>'.implode(', ', $listMembers).'</p>':null);
               
               switch($task->type()){
                    case 'bug':
                         $libelle_type  = 'Bug';
                         $initiaux_type = 'SB';
                         $bgcolor       = '#ffadad';
                         $nbb++;
                         break;
                    case 'suivi':
                         $libelle_type  = 'Tâche';
                         $initiaux_type = 'SI';
                         $bgcolor       = '#CCFFAA';
                         $nbt++;
                         break;
               }
               
               $duree_libelle = '';
               if($task->duration() > 0) $duree_libelle = $task->duration().'h';
               if($task->duration_real() > 0)
               {
                    if($duree_libelle != '') $duree_libelle .= '/';
                    $duree_libelle .= $task->duration_real().'h';
               }

               $tasks .= ""
               . "<tr>"
                    . "<td style=\"white-space: normal; background-color: ".$bgcolor.";\">"
                         . $libelle_type
                         . "<br>"
                         . $taskId
                    . "</td>"
                    //. "<td style=\"white-space: normal;\">".$task->initialDate()->format('d/m/Y')."</td>"
                    . "<td style=\"white-space: normal;\"><a href=\"".$link."\" title=\"".$lnkTitle."\">".$task->name()."</a></td>"
                    /*. "<td style=\"white-space: normal;\"><a href=\"".$link."\" title=\"".$lnkTitle."\">". htmlentities($task->shortDesc(),ENT_COMPAT,'ISO-8859-15')."</a></td>"*/
                    //. "<td style=\"white-space: normal;\">".$listCategory[$task->category()]."</td>"
                    . "<td class=\"members\">"
                         . "<p class=\"incharge\">".$owner."</p>"
                    . "</td>"
                    . "<td class=\"members\">"
                         . "<p class=\"incharge\">".$inCharge."</p>"
                    . "</td>"
                    . "<td style=\"white-space: normal;\" class=\"members\">"
                         . "<ul style=\"padding: 0 0 0 20px; margin: 0;\">"
                         . $taskMembers
                         . "</ul>"
                    . "</td>"
                    . "<td style=\"white-space: normal;\">".$task->exceptedDate()->format('d/m/Y')."</td>"
                    . "<td style=\"white-space: normal; text-align: center;\">" . $duree_libelle  ."</td>"
                    . "<td style=\"white-space: normal;\" class=\"priority_".strtolower($task->priority_name())."\">".$task->priority_name()."</td>"
                    . "<td style=\"text-align: center;\" class=\"status ".$task->status()."\">".$listStatus[$task->status()]."</td>"
               . "</tr>";
          }

          $fleche_bas = "<span id=\"sorttable_sortfwdind\">&nbsp;&#9662;</span>";
          $fleche_haut = "<span id=\"sorttable_sortfwdind\">&nbsp;&#9652;</span>";

          $aTriBasic = array(  
               'asc' => array(
                    'order' => 'desc',
                    'arrow' => $fleche_bas
               ),
               'desc' => array(
                    'order' => 'asc',
                    'arrow' => $fleche_haut
               )
          );

          $aTabTri = array(
               'type'        => array(
                    'tri'     => $aTriBasic,
                    'libelle' => 'Type<br>N°'
               ),
               'intitule'    => array(
                    'tri'     => $aTriBasic,
                    'libelle' => 'Intitulé'
               ),
               'demandeur'   => array(
                    'tri'     => $aTriBasic,
                    'libelle' => 'Demandeur'
               ),
               'responsable' => array(
                    'tri'     => $aTriBasic,
                    'libelle' => 'Responsable'
               ),
               'datefin'     => array(
                    'tri'     => $aTriBasic,
                    'libelle' => 'Date fin'
               ),
               'duree'       => array(
                    'tri'     => $aTriBasic,
                    'libelle' => 'Temps<br>est./réel'
               ),
               'priorite'    => array(
                    'tri'     => $aTriBasic,
                    'libelle' => 'Priorité'
               ),
               'etat'        => array(
                    'tri'     => $aTriBasic,
                    'libelle' => 'État'
               )
          );

          //La fonction appelée est différente selon le tableau que l'on souhaite trier
          $aFuncTri = array(
               'classic' => 'load_liste',
               'owner' => 'load_liste_owner'
          );

          //Paramétrage des champs triables
          foreach($aTabTri as $champs => $vars)
          {
               $arrow = '';
               $order = 'asc';
               if($this->tm->taskSettings('tri_type') == $champs)
               {
                    $arrow = $aTabTri[$champs]['tri'][$this->tm->taskSettings('tri_order')]['arrow'];
                    $order = $aTabTri[$champs]['tri'][$this->tm->taskSettings('tri_order')]['order'];
               }
               $aEntetes[$champs] = "<th class=\"entete\" onclick=\"" . $aFuncTri[$this->type_suivi] . "('" . $champs . "','" . $order . "');\">" . $aTabTri[$champs]['libelle'] . $arrow . "</th>";
          }

          $return = ""
          . "<table class=\"sortable table_alternate_color\">"
               . "<tr>"
                    . $aEntetes['type']
                    . $aEntetes['intitule']
                    . $aEntetes['demandeur']
                    . $aEntetes['responsable']
                    . "<th class=\"entete\">Intervenant(s)</th>"
                    . $aEntetes['datefin']
                    . $aEntetes['duree']
                    . $aEntetes['priorite']
                    . $aEntetes['etat']
               . "</tr>"
               . $tasks
          . "</table>"
          . "<p>Temps estimé (à traiter & en cours) : ".$totalduration." h</p>";
          
          return $return;
     }

     function showTask($taskid){
          
          global $remotes;

          $this->taskId = $taskid;
          
          $this->pushJs('status');
          $this->filebox();
          $task = $this->tm->getTask($taskid);
          $this->tm->log->setTaskId($taskid);
    
          if (!$task) return (false);

          $dateFormat = '%A %e %B %Y';
          $members    = $this->tm->members(false);

          $type      = $task->type();
          $id        = $this->taskId($taskid);
          $name      = $task->name();
          $shortDesc = $task->shortDesc();
          $inCharge  = $task->inCharge();
          $inCharge  = (array_key_exists($inCharge, $members))? $members[$inCharge] : $inCharge;
          $inCharge  = $inCharge;
          $startDate = strftime($dateFormat, $task->startDate()->format('U')); 
          $initDate  = strftime($dateFormat, $task->initialDate()->format('U'));
          $excepDate = strftime($dateFormat, $task->exceptedDate()->format('U'));
          
          if($task->duration()>0) $duration="<br>Durée estimée : ".$task->duration()."h";
          if($task->duration_real()>0) $duration_real="<br>Durée réelle : ".$task->duration_real()."h";

          $taskMembers = $task->members();
          
          if (!empty($taskMembers)){
               foreach ($taskMembers as $member){
                    $listMembers .= '<li>'.(array_key_exists($member, $members))? $members[$member] : $member.'</li>';
               }
          }
          
          $longDesc = $task->longDesc();
          $taskLink = $this->showLink($task);
          $taskLogs = $this->tm->log->msglist();

          $trLogs = array();
          if (!empty($taskLogs)){
          
               foreach($taskLogs as $date=>$log) {
                    //$author = (array_key_exists($log['author'], $members))? $members[$log['author']] : $log['author'];

                    $author = $log['author'];
                    $trLogs[$date] .= ''
                    .'<tr class="tasklog_'.$log['type'].'">'
                         .'<td>'.$this->suiviicon[$log['moyen']].date("d/m/Y H:i",strtotime($log['date'])).'</td>'
                         .'<td>'.$author.'</td>'
                         .'<td>'.$this->logDest($log['type'],$log['destinataire']).'</td>'
                         .'<td>'.formatsuivi(($log['type']=='msg')?$log['message']:$log['message']).($log['rappel']>0?'<p>&#9755; Rappel le '.date("d/m/Y H:i",$log['rappel']).' pour '.$log['pour'].'</p>':'').'</td>'
                    .'</tr>';
               }
          }

          $taskLogs = $this->tm->log->emaillist();
          
          if(!empty($taskLogs)){
               
               foreach($taskLogs as $date=>$mail) $trLogs[$date].=$this->formatmail($mail);
          }
	

          $remote = $task->remote();
          
          if(count($remote) > 0) {
               
               $sendtask = new send_task($remotes);
    
               $remotelist = ""
               . "<div class=\"tasklink\">"
                    . "<h3>Ce suivi est présent sur :</h3>";
               
               foreach($remote as $site => $rtaskid){
                    
                    $remotelist .= "<a href=\"".$remotes[$site]."/task.php?a=showTask&task=".$rtaskid."\" target=\"_blank\">".$site." #".$rtaskid."</a><br>";
                    $logs    = $sendtask->get_log($site,$rtaskid);
                    $acronym = $this->acronym($site);
                    
                    foreach($logs[$site] as $ts => $log){
                         
                         $trLogs[$ts] .= ''
                         . '<tr class="tasklog_'.$log['type'].'">'
                              . '<td>'.$this->suiviicon[$log['moyen']].date("d/m/Y H:i",strtotime($log['date'])).'</td>'
                              . '<td>'.$log['author'].' ('.$acronym.')</td>'
                              . '<td>'.$this->logDest($log['type'],$log['destinataire']).'</td>'
                              . '<td>'.formatsuivi(($log['type']=='msg')?$log['message']:$log['message']).($log['rappel']>0?'<p>&#9755; Rappel le '.date("d/m/Y H:i",$log['rappel']).' pour '.$log['pour'].'</p>':'').'</td>'
                         . '</tr>';
                    }
               }
	
               $remotelist .= "</div>";
          }
    
          krsort($trLogs);
          $listLogs=implode("",$trLogs);

          $listStatus    = $task->listStatus();
          $status        = $task->status();
          $change_status = $this->change_state($task);
          $editButton    = $this->button($task, 'editTask', 'Modifier');

          if($initDate != $excepDate){
               $initWord  = 'initialement';
               $reportMsg = ', reporté au <strong>'.$excepDate.'</strong>';
               $initStyle = 'style="color:orange"';
          }
    
          $file_nb = $this->file_nb($task);
          
          $return = ""
          . "<div class=\"showtask\">"
               . "<h2>".$id."&nbsp;: ".$name."</h2>"
               //. "<h3>".$shortDesc."</h3>"
               . "<p class=\"status ".$status."\" onclick=\"show_status(this)\">".$listStatus[$status]."</p>
	 $change_status
    <span onclick=\"fb.show('taskmanager/fichiers.php?taskid=$taskid');\"
          class=\"fileboxbutton\">
      <img src=\"images/icon/pdf.png\">
      Voir les fichiers joints ($file_nb)
    </span>
	<p class=\"date\">
	  Créé le <strong>$startDate</strong>, dû $initWord pour le
	  <strong $initStyle>$initDate</strong>$reportMsg
	  ".$duration."
       ".$duration_real."
	</p>
	<div class=\"clear\"></div>
	<div class=\"wrap\">
	  <div class=\"members\">
	    <h4>Responsable&nbsp;:</h4>
	    <ul><li>$inCharge</li></ul>
	    <h4>Intervenants&nbsp;:</h4>
	    <ul>$listMembers</ul>
	  </div>
	  <div class=\"desc\">
	    $longDesc
	  </div>
	</div>
	<div class=\"clear\"></div>
	<form action=\"?\" method=\"get\">
	  $editButton
	  <input type=\"hidden\" name=\"task\" value=\"$taskid\" />
	  <input type=\"hidden\" name=\"$type\" value=\"1\" />
    </form>
    $taskLink
    $remotelist
	<div class=\"tasklog\">
	  <h3>Suivi:</h3>
	  <form method=\"post\" action=\"?a=addLog&amp;task=$taskid\">
	    <input type=\"hidden\" name=\"moyen\">
	    <input type=\"hidden\" name=\"type\">
	    <input type=\"hidden\" name=\"destinataire\">

	    <table>
	      <tr>
		<td>Rechercher un client ou un fournisseur :<br><input type=\"text\" onkeyup=\"srcfournisseur(this.value,$taskid);\"><div id=\"srcfournisseur\"></div></td>
		<td><a href=\"javascript:suiviauto('','',0,'Message interne')\">Message interne</a></td>
	      </tr>
	    </table>

	    <div id=\"suividiv\">
		<div id=\"suivititle\"></div>
		<textarea id =\"tasklogmsg\" name=\"message\"></textarea>
		<br>Date de rappel : <input type=\"text\" name=\"rappel\" id=\"rappel\" onclick=\"calendrier_show(this.id,this.value,'planning',planningcb);\" readonly> pour <input type=\"text\" name=\"pour\" size=\"6\" readonly> <input type=\"checkbox\" name=\"box\" value=\"1\" checked> Afficher le rappel
		<p><input type=\"submit\" value=\"Ajouter le suivi\" /></p>
	    </div>
	  </form>
	  <table class=\"sortable\">
	  <tr>
	    <th style=\"width:15%\">Date<span id=\"sorttable_sortfwdind\">&nbsp;&#9652;</span></th>
	    <th>Auteur</th>
	    <th>Destinataire</th>
	    <th style=\"width:60%\">Message</th>
	  </tr>
	  $listLogs
	  ${$this->oldLogs($taskid)}
	  </table>
	</div>
      </div>";
          
     return $return;
}

     function chooseRemotes($task=null) {
         global $remotes;
         global $DB_site2;

         $sentremotes=array();
         if(!is_null($task)) $sentremotes=$task->remote();
         $send=new send_task($remotes);
         $remoteusers=$send->get_users();
         $html="";
         if(count($remoteusers)>0) $html="<br><label>Transmettre sur :</label>";
         foreach($remoteusers as $site=>$result){
             if(isset($result['member']) && in_array($this->tm->user(),$result['member']))
                 if(!is_null($sentremotes) && array_key_exists($site,$sentremotes))
                     $html.="<a href=\"".$remotes[$site]."task.php?a=showTask&task=".$sentremotes->$site."\" target=\"_blank\"><img src=\"images/coche_verte.svg\" width=\"20\">".$site."</a></br>";
                 else
                     $html.="<input type=\"checkbox\" name=\"remote[$site]\" value=\"".$remotes[$site]."\"> ".$site."<br>";
         }

         return $html;
     }
  
  // Show linked stuff
  // TODO: move data fetching to TaskTemplate or create TaskLink
  function showLink($task) {
    global $DB_site2;
    $id   = $task->link();
    $type = $task->linkType();
    if (is_null($id) || is_null($type))
      return (null);

    switch($type) {
    case 'article':
      $q = $DB_site2->query('
        SELECT `artcode`, `libelleFR`
        FROM `article`
        WHERE `artid` LIKE "'.mysqli_real_escape_string($DB_site2->link_id,$task->link()).'"
      ');
      if ($q && $r = $DB_site2->fetch_array($q))
        $htmllink = '
          <img src="/configurations/images/produits/br/'.$id.'.jpg" />
          <h3><a href="ajout_produit.php?artid='.$id.'&amp;prec=modif">'
            .$r['artcode'].'</a></h3>
          <p>'.$r['libelleFR'].'</p>
          <div class="clear"></div>';
      break ;
    case 'facture':
      $q = $DB_site2->query('
        SELECT `facturid`, `prenom`, `nom`
        FROM `facture`
        WHERE `facturid` LIKE "'.mysqli_real_escape_string($DB_site2->link_id,$task->link()).'"
      ');
      if ($q && $r = $DB_site2->fetch_array($q))
        $htmllink = '
          <h3><a href="facture.php?action=editer&amp;facturid='.$id.'">
          Commande #'.$r['facturid'].'</a></h3>
          <p>Passée par '.$r['prenom'].' '.$r['nom'].'</p>
          <div class="clear"></div>';
      break ;
    case 'fournisseur':
      $q = $DB_site2->query('
        SELECT `societe`
        FROM `fournisseur`
        WHERE `societe` LIKE "'.mysqli_real_escape_string($DB_site2->link_id,$task->link()).'"
      ');
      if ($q && $r = $DB_site2->fetch_array($q))
        $htmllink = '
        <h3><a href="fournisseurs_liste.php?action=editer&amp;fournisseur='
          .$id.'">
          Fournisseur '.$r['societe'].'</a></h3>
          <div class="clear"></div>';
      break ;
    case 'utilisateur':
      $q = $DB_site2->query('
        SELECT `userid`, `nom`, `prenom`, `adresse`, `codepostal`, `ville`
        FROM `utilisateur`
        WHERE `userid` LIKE "'.mysqli_real_escape_string($DB_site2->link_id,$task->link()).'"
      ');
      if ($q && $r = $DB_site2->fetch_array($q))
        $htmllink = '
        <h3><a href="utilisateur.php?action=editer&amp;userid='
          .$id.'">
           '.$r['prenom'].' '.$r['nom'].'</a></h3>
           <p>'.$r['adresse'].'<br />'.$r['codepostal'].' '
           .$r['ville'].'</p>
          <div class="clear"></div>';
      break ;
    }
    
    $html="";
    if(!is_null($htmllink)){
	$html =  '<div class="tasklink">';
	$html .= '<h3>Cet article est lié à&nbsp;:</h3>';
	$html .= $htmllink;
	$html .=  '</div>';
    }
    return ($html);
  }

  // Based on "suivi des commandes"
  function oldLogs($taskid) {
    global $DB_site2;

    return ;

    // suivi client
    //$suivi=$DB_site2->query_first("SELECT suivi,suivi2,advdate1,advdate2,advdate3 FROM facture WHERE facturid=$facturid");

    $usertel=($user_info['tel']!=0);

    $suiviicon=array("tel_out"=>"<img src=\"images/tel_out.png\"><br>","tel_in"=>"<img src=\"images/tel_in.png\"><br>","mail_in"=>"<img src=\"images/get_mail.png\"><br>");
    $suivitd=$this->suivi_commande($taskid,$savlinks);

    print "<form action=\"facture.php?action=editer&facturid=$taskid\" method=\"post\">
    <input type=\"hidden\" name=\"moyen\">
    <input type=\"hidden\" name=\"type\">
    <input type=\"hidden\" name=\"destinataire\">
    <hr style=\"width:60%;margin:20px;\">
    <a name=\"suivitop\"></a>
    <div class=\"table_modules_boutiques\">
    <p><b>Suivi commercial</b></p>
    <table id=\"suiviaction\">
    <thead>
    <tr><td class=\"suiviclient\">Facturation client</td><td class=\"suiviclient\">Livraison client</td><td class=\"suivipro\">Fournisseur</td><td class=\"suiviint\">Interne</td></tr>
    </thead>
    <tr>
	<td class=\"suiviclient\"><span class=\"suivilink\" onclick=\"suiviauto('tel_out','client',$user[userid],'Appel téléphone client',".($usertel?"'$user[telephone]'":"undefined").");\">".epigy($user[telephone])."</span> <span class=\"suivilink\" onclick=\"suiviauto('tel_out','client',$user[userid],'Appel téléphone client',".($usertel?"'$user[portable]'":"undefined").");\">".epigy($user[portable])."</span></td>
	<td class=\"suiviclient\"><span class=\"suivilink\" onclick=\"suiviauto('tel_out','facture',$rs_facture[facturid],'Appel téléphone livraison',".($usertel?"'$rs_facture[telephone]'":"undefined").");\">".epigy($rs_facture[telephone])."</span> <span class=\"suivilink\" onclick=\"suiviauto('tel_out','facture',$rs_facture[facturid],'Appel téléphone livraison',".($usertel?"'$rs_facture[portable]'":"undefined").");\">".epigy($rs_facture[portable])."</span></td>
	<td class=\"suivipro\" rowspan=\"3\"><input type=\"text\" onkeyup=\"srcfournisseur(this.value);\"><div id=\"srcfournisseur\"></div></td>
	<td class=\"suiviint\" rowspan=\"3\"><span class=\"suivilink\" onclick=\"suiviauto('','',0,'Suivi interne')\">Message interne</span></td>
    </tr>
    <tr>
	<td class=\"suiviclient\"><span class=\"suivilink\" onclick=\"mail_auto(undefined,'".$user['mail']."');mail_services('$user[langue]','adv,commercial');mail_suivi('suivi_commande',$rs_facture[facturid]);suivi_hide();\">".$user['mail']."</span></td>
	<td class=\"suiviclient\"><span class=\"suivilink\" onclick=\"mail_auto(undefined,'".$rs_facture['mail']."');mail_services('$user[langue]','adv,commercial');mail_suivi('suivi_commande',$rs_facture[facturid]);suivi_hide();\">".$rs_facture['mail']."</span></td>
    </tr>
    <tr>
	<td class=\"suiviclient\"><span class=\"suivilink\" onclick=\"suiviauto('tel_in','client',$user[userid],'Appel client reçu');\">Appel reçu</span> <span class=\"suivilink\" onclick=\"suiviauto('mail_in','client',$user[userid],'Email client reçu');\">Email reçu</span></td>
	<td class=\"suiviclient\"><span class=\"suivilink\" onclick=\"suiviauto('tel_in','facture',$rs_facture[facturid],'Appel livraison reçu');\">Appel reçu</span> <span class=\"suivilink\" onclick=\"suiviauto('mail_in','facture',$user[userid],'Email client reçu');\">Email reçu</span></td>
    </tr>
    </table>
    <div id=\"suividiv\" style=\"display:none;\">
      <br><div id=\"suivititle\"></div>
      <textarea name=\"suivi\" id=\"suivi\" cols=\"60\" rows=\"8\"></textarea>
      <br>Date de rappel : <input type=\"text\" name=\"rappel\" id=\"rappel\" onclick=\"calendrier_show(this.id,this.value,'planning',planningcb);\" readonly> pour <input type=\"text\" name=\"pour\" size=\"6\" readonly> <input type=\"checkbox\" name=\"box\" value=\"1\" checked> Afficher le rappel
      <p><input type=\"submit\" value=\"Ajouter le suivi\"></p>
    </div>
    Filtrer : <span class=\"suiviint\"><input type=\"checkbox\" name=\"filterint\" onclick=\"filtersuivi();\" checked> Interne</span> <span class=\"suiviclient\"><input type=\"checkbox\" name=\"filterclient\" onclick=\"filtersuivi('client',this.checked);\" checked> Client</span> <span class=\"suivipro\"><input type=\"checkbox\" name=\"filterpro\" onclick=\"filtersuivi('pro',this.checked);\" checked> Fournisseur</span><br>";
//     foreach($savlinks as $savlink) print " <span style=\"background-color:".$linkcolor[$savlink].";".($savlink==$facturid?"color:red;":"")."\"><input type=\"checkbox\" name=\"savlinks\" value=\"$savlink\" onclick=\"filtersuivi();\" checked> ".$savlink."</span>";
    print "<table id=\"tablesuivi\">
    <tr><td colspan=\"3\" align=\"center\">$suivi[0]</td></tr>
    <tr><td width=\"50\"></td><td></td><td></td></tr>";

    if(isset($suivitd) && is_array($suivitd)){
      krsort($suivitd);
      foreach($suivitd as $tr) print $tr;
    }
    print "</table></div>
    </form>";

//     $suivitd=array();
//     $requete=$DB_site2->query("SELECT * FROM suivi_interne WHERE userid=$rs_facture[userid] ORDER BY date DESC");
//     while($suivi=$DB_site2->fetch_array($requete)){
//       $causeperte="";
//       switch($suivi['etat']){
// 	  case 0:
// 	  $etat="<span style=\"color:red;\">Client perdu</span>";
// 	  $cause=$DB_site2->query_first("SELECT * FROM causeperte WHERE date=$suivi[date] AND userid=$rs_facture[userid]");
//
// 	  if($cause['article']!="" || $cause['marque']!=""){
// 	      $causeperte.="<br>Achat ".$cause['article']." ".$cause['marque'];
// 	      if($cause['prix']!="") $causeperte.=" prix : ".$cause['prix'];
// 	  }
// 	  if($cause['lieu']!=""){
// 	      if($causeperte=="") $causeperte="<br>Achat";
// 	      $causeperte.=" chez ".$cause['lieu'];
// 	  }
// 	  switch($cause['cause']){
// 	    case 0:
// 	    $etat.="<br>(cause prix)";
// 	    break;
// 	    case 1:
// 	    $etat.="<br>(cause délai)";
// 	    break;
// 	    case 2:
// 	    $etat.="<br>(cause projet abandonné)";
// 	    break;
// 	    case 3:
// 	    $etat.="<br>(trop relancé)";
// 	    break;
// 	    case 4:
// 	    $etat.="<br>(mauvais rapports commerciaux)";
// 	    break;
// 	    case 5:
// 	    $etat.="<br>(ne veux pas dire)";
// 	    break;
// 	    case 6:
// 	    $etat.="<br>(autre)";
// 	    break;
// 	  }
// 	  break;
// 	  case 1:
// 	  $etat="<span style=\"color:#0000AA;\">En cours</span>";
// 	  break;
// 	  case 2:
// 	  $etat="<span style=\"color:#0000FF;\">Presque sûr</span>";
// 	  break;
// 	  case 3:
// 	  $etat="<span style=\"color:#000088;\">Peu probable</span>";
// 	  break;
//       }
//       $suivitd[$suivi['date']]="<tr>
//       <td>".date("d/m/Y H:i",$suivi['date'])."</td>
//       <td>".$suivi['commercial']."</td>
//       <td>".formatsuivi($suivi['suivi']).$causeperte."</td>
//       <td>$etat</td>
//       <td>".($suivi['rappel']!=0?date("d/m/Y H:i",$suivi['rappel'])." par ".$suivi['pour']:"")."</td>
//       </tr>";
//     }
//     $requete=$DB_site2->query("SELECT * FROM mail_outbox WHERE suivitype='suivi_client' AND suiviid=$rs_facture[userid]");
//     while($mail=$DB_site2->fetch_array($requete)){
// 	$suivitd[$mail['date']]="<tr>
// 	<td>".date("d/m/Y H:i",$mail['date'])."</td>
// 	<td>".$mail['utilisateur']."</td>
// 	<td>Email envoyé à ".$mail['to']."<br>".$mail['subject']."<br>".formatsuivi($mail['body']).($mail['pj']!=""?"<br>PJ : ".$mail['pj']:"")."</td>
// 	<td></td>
// 	<td></td>
// 	</tr>";
//     }
//     if(count($suivitd)==0) $suivitd[]="<tr><td colspan=\"5\" align=\"center\">Pas de suivi</td></tr>";
//     if(isset($suivitd)){
//       echo "<table class=\"table_factures\">
//       <thead style=\"color:black; text-align:center; font-weight:bold;\">
//       <tr><td colspan=\"5\" align=\"center\"><b>Suivi client</b></td></tr>
//       <tr><td>Date</td><td>Commercial</td><td>Suivi</td><td>Etat</td><td>Rappel</td></tr>
//       </thead>";
//       krsort($suivitd);
//       foreach($suivitd as $tr) print $tr;
//       echo "</table>";
//     }
  }

  // Part of "suivi des commandes"
  function suivi_commande($taskid,$savlinks){
    global $DB_site2;
    global $suiviicon;
    global $usertel;
    global $linkcolor;
    $suivitd=array();

    $rq_suivi=$DB_site2->query("SELECT * FROM suivi_interne_log WHERE task_id='$taskid' ORDER BY date DESC");
    while($s=$DB_site2->fetch_array($rq_suivi)){
	$contact=array();
	$bgcolor="#dddddd;";
	$tddest="";
	if($s['type']=="") $s['type']="int";
	switch($s['type']){
	    case "pro":
	    $contact=$DB_site2->query_first("SELECT * FROM fournisseur WHERE id=".$s['destinataire']);
	    break;
	    case "client":
	    $contact=$DB_site2->query_first("SELECT *,userid AS id FROM utilisateur WHERE userid=".$s['destinataire']);
	    break;
	    case "facture":
	    $contact=$DB_site2->query_first("SELECT *,facturid AS id FROM facture WHERE facturid=".$s['destinataire']);
	    $s['type']="client";
	    break;
	}
	$tddest=$contact['societe']." ".$contact['prenom']." ".$contact['nom']."
	<br><span class=\"suivilink\" onclick=\"mail_auto(undefined,'".$contact['mail']."');mail_suivi('suivi_commande',$facturid);\">".$contact['mail']."</span>
	<br><span class=\"suivilink\" onclick=\"suiviauto('tel_out','".$s['type']."',".$contact['id'].",'Appel téléphone ".$contact['societe']." (".$contact['nom']." ".$contact['prenom'].")',".($usertel?"'$contact[telephone]'":"undefined").");\">".epigy($contact['telephone'])."</span>";
	if($contact['portable']!="") $tddest.="<br><span class=\"suivilink\" onclick=\"suiviauto('tel_out','".$s['type']."',".$contact['id'].",'Appel téléphone ".$contact['societe']." (".$contact['nom']." ".$contact['prenom'].",".($usertel?"'$contact[portable]'":"undefined").")');\">".epigy($contact['portable'])."</span>";

	$suivitd[$s['date']]="<tr id=\"".$s[type]."-".$s[id]."-".$s['facturid']."\" class=\"suivi$s[type]\"><td><span style=\"background-color:".$linkcolor[$s['facturid']].";".($s['facturid']==$facturid?"color:red;":"")."\">".$s['facturid']."</span><br>".$suiviicon[$s['moyen']].date("d/m/Y H:i",$s['date'])."</td><td>".$s['commercial']."</td><td>".$tddest."</td><td class=\"suivitxt\">".formatsuivi($s['suivi'])."</td><td".($s['rappel']>$today && $s['rappel']<$tomorrow?" style=\"color:red;\"":"").">".($s['rappel']!=0?"A rappeler le ".date("d/m/Y à H:i",$s['rappel'])." par ".$s['pour']:"")."</td></tr>";
    }
    $requete=$DB_site2->query("SELECT * FROM mail_outbox WHERE suivi_interne_id=$taskid");
    while($mail=$DB_site2->fetch_array($requete)){
	if(isset($suivitd[$mail['date']])) $mail['date']++;
	$suivitd[$mail['date']]=formatmail($mail,"out");
    }
    $requete=$DB_site2->query("SELECT * FROM mail_inbox WHERE suivi_interne_id = $taskid");
    while($mail=$DB_site2->fetch_array($requete)){
	$suivitd[$mail['date']]=formatmail($mail,"in");
    }

    return $suivitd;
  }

  private function logDest($desttype,$destid){
      global $DB_site2;
      global $usertel;

      switch($desttype){
	  case "":
	  return "";

	  case "client":
	  $contact=$DB_site2->query_first("SELECT *,userid AS id FROM utilisateur WHERE userid=".$destid);
	  break;

	  case "pro":
	  $contact=$DB_site2->query_first("SELECT * FROM fournisseur WHERE id=".$destid);
	  break;
      }

      $return=$tddest=$contact['societe']." ".$contact['prenom']." ".$contact['nom']."
	<br><span class=\"suivilink\" onclick=\"mail_auto(undefined,'".$contact['mail']."');mail_suivi('suivi_interne',".$this->taskId.");\">".$contact['mail']."</span>
	<br><span class=\"suivilink\" onclick=\"suiviauto('tel_out','".$desttype."',".$contact['id'].",'Appel téléphone ".$contact['societe']." (".$contact['nom']." ".$contact['prenom'].")',".($usertel?"'$contact[telephone]'":"undefined").");\">".epigy($contact['telephone'])."</span>";
	if($contact['portable']!="") $tddest.="<br><span class=\"suivilink\" onclick=\"suiviauto('tel_out','".$desttype."',".$contact['id'].",'Appel téléphone ".$contact['societe']." (".$contact['nom']." ".$contact['prenom'].",".($usertel?"'$contact[portable]'":"undefined").")');\">".epigy($contact['portable'])."</span>";

      return $return;
  }

  private function formatmail($mail){
      global $DB_site2;

      $tddest="";
      $type="int";
      $contact=array();
      $source=$mail['to'];
      if($sens=="in") $source=$mail['from'];
      $mails=preg_split("[,|;]",$source);
      foreach($mails as $mailto){
	  $user=$DB_site2->query_first("SELECT userid,mail,telephone,portable,nom,prenom,raisonsociale AS societe FROM utilisateur WHERE mail<>'' AND mail='".addslashes($mailto)."' AND code411=''");
	  $contact=$DB_site2->query_first("SELECT * FROM fournisseur WHERE mail<>'' AND mail='".addslashes($mailto)."'");
	  if($mailto==$contact['mail']){
	      $tddest.="<div class=\"suividest\">".$contact['societe']." ".$contact['prenom']." ".$contact['nom']."
	      <br><span class=\"suivilink\" onclick=\"mail_auto(undefined,'".$contact['mail']."');mail_suivi('suivi_commande','".$mail['suiviid']."')\">".$contact['mail']."</span>
	      <br><span class=\"suivilink\" onclick=\"suiviauto('tel_out','pro',".$contact['id'].",'Appel téléphone ".$contact['societe']." (".$contact['nom']." ".$contact['prenom'].",".($usertel?"'$contact[telephone]'":"undefined").")');self.location.hash='#suivitop';\">".epigy($contact['telephone'])."</span>";
	      if($contact['portable']!="") $tddest.="<br><span class=\"suivilink\" onclick=\"suiviauto('tel_out','pro',".$contact['id'].",'Appel téléphone ".$contact['societe']." (".$contact['nom']." ".$contact['prenom'].")',".($usertel?"'$contact[portable]'":"undefined").");self.location.hash='#suivitop';\">".epigy($contact['portable'])."</span>";
	      $tddest.="</div>";
	      if($type=="int") $type="pro";
	  }elseif($mailto==$user['mail']){
	      $tddest.="<div class=\"suividest\">".$user['societe']." ".$user['prenom']." ".$user['nom']."
	      <br><span class=\"suivilink\" onclick=\"mail_auto(undefined,'".$user['mail']."');mail_services('$user[langue]','adv,commercial');mail_suivi('suivi_commande','".$mail['suiviid']."');\">".$user['mail']."</span>
	      <br><span class=\"suivilink\" onclick=\"suiviauto('tel_out','client',".$user['userid'].",'Appel téléphone ".$user['societe']." (".$user['nom']." ".$user['prenom'].")',".($usertel?"'$user[telephone]'":"undefined").");self.location.hash='#suivitop';\">".epigy($user['telephone'])."</span>";
	      if($user['portable']!="") $tddest.="<br><span class=\"suivilink\" onclick=\"suiviauto('tel_out','client',".$user['userid'].",'Appel téléphone ".$user['societe']." (".$user['nom']." ".$user['prenom'].")',".($usertel?"'$user[portable]'":"undefined").");self.location.hash='#suivitop';\">".epigy($user['portable'])."</span>";
	      $tddest.="</div>";
	      $type="client";
	  }else{
	      $tddest.="<div class=\"suividest\">".$mailto."</div>";
	      $type="";
	  }
      }


      return "<tr id=\"".$type."-".$mail['id']."-".$mail['suiviid']."\" class=\"tasklog_$type\"><td><img src=\"images/send_mail.png\"><br>".date("d/m/Y H:i",$mail['date'])."</td><td>".$mail['utilisateur']."</td><td>".$tddest."</td><td>".$mail['subject']."<br>".formatsuivi($mail['body']).($mail['pj']!=""?"<br>PJ : ".$mail['pj']:"")."</td><td></td></tr>";
  }

     private function select($id, $name, $list, $default = null, $isMultiple = false) {
          
          if(!is_array($list)) return (false);

          $multiple = (($isMultiple)? 'multiple' : null);

          $html = '<select id="'.$id.'" name="'.$name.'" '.$multiple.'>';

          foreach ($list as $value => $text) {
               
               $value	= $value;
               $text	= $text;
               $selected = (($value == $default)? 'selected' : null);
               $html	.= '<option value="'.$value.'" '.$selected.'>'.$text.'</option>';
          }
          
          $html	.= '</select>';
          
          return $html;
     }

     private function input($id, $name, $value = '', $hidden = false) {

          $html = '<input type="'.($hidden ? 'hidden' : 'text').'" name="'.$name.'" id="'.$id.'" value="'.$value.'">';
          
          return $html;
     }
  
  private function button($task, $id, $value) {
//     if ($this->tm->checkPerm($task, $id))
     return ('<button type="submit" name="a" value="'.$id.'">'.$value.'</button>');
//     return (null);
  }
     private function taskid($id) {
          
          $tabTask = $this->tm->getTask($id);
          
          $prefix   = '';
          if(isset($tabTask->type) && !empty($tabTask->type)){
               switch($tabTask->type){
                    case 'bug':
                         $prefix   = 'SB#';
                         break;
                    case 'suivi':
                         $prefix   = 'SI#';
                         break;
               }
          }
          
          return ($prefix.$id);
     }
 
  private function file_nb($task) {
    global $DB_site2;
    //compte seulement les fichiers et non les dossiers :
    $count=$DB_site2->query_first("SELECT COUNT(*) FROM suivi_interne_files WHERE taskid=".intval($task->id())." AND ext != ''");
    return intval($count[0]);
  }
  
  private function acronym($txt) {
    $expr = '/(?<=\s|^)[a-z]/i';
    preg_match_all($expr, $txt, $matches);

    $result = implode('', $matches[0]);

    $result = strtoupper($result);
    
    return $result;
  }
  
  private function change_state($task){
    $statuslist=$task->listStatus();
    $taskid=$task->id();
    $statusdiv="";
    foreach($statuslist as $name=>$txt){
		if($name!="closed"){
			$statusdiv.="<div class=\"status $name\" onclick=\"update_state('$name')\">$txt</div>";
		} else {
			if ($this->tm->checkPerm($task, 'closeTask', $this->user)){
				$statusdiv.="<div class=\"status $name\" onclick=\"update_state('$name')\">$txt</div>";
			}
		}
	}
    return <<<HTML
    <div id="statuslist" class="statuslist">
    $statusdiv
    </div>
   <form name="change_state" action="task.php?a=editTask&task=$taskid" method="post">
   <input type="hidden" name="status">
   </form>
HTML;
  }

};

?>
