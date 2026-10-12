<?php

require_once(__DIR__.'/task.class.php');
require_once(__DIR__.'/taskLog.class.php');

class TaskManager {
     
     private $db;
     private $user;
     private $taskList;
     private $taskSettings;
     
     public  $log;

     // For error and success notifications
     private $errors;
     private $success;

     function __construct($db, $taskSettings, $user) {
          
          if (!is_a($db, 'DB_Sql')) throw new Exception('DB_Sql attendu');
            
          $this->db           = $db;
          $this->user         = $user;
          $this->taskSettings = $taskSettings;
          
          $this->errors  = array();
          $this->success = array();

          $this->log = new TaskLog($db, null, $user);
     }

     // Getters
     function taskSettings($setting) {
          
          if (!array_key_exists($setting, $this->taskSettings)) return (false);
            
          return ($this->taskSettings[$setting]);
     }
     
     function errors() {
          return ($this->errors);
     }
     
     function success() {
          return ($this->success);
     }
     
     function user() {
          return ($this->user);
     }

  // Methods
  function pushError($errMsg) {
    array_push($this->errors, $errMsg);
  }
  function pushSuccess($successMsg) {
    array_push($this->success, $successMsg);
  }

     function getTask($taskId){
          
          try {
               if (!preg_match('/(\d+)/', $taskId)) throw new Exception('Numéro de suivi invalide');
               
               $sql = "SELECT COUNT(id)";
               $sql .= " FROM suivi_interne";
               $sql .= " WHERE id = ".$taskId;
               $count = $this->db->query_first($sql);
             
               if ($count && $count[0] == 0) throw new Exception('Ce suivi n\'existe pas');
             
               $sql = "SELECT *";
               $sql .= " FROM `suivi_interne`";
               $sql .= " WHERE `id` = ".$taskId;
               $taskData = $this->db->query_first($sql);
             
               if (!$taskData) throw new Exception('Requête vers la base de données échouée');
               
               $task = new Task($this->db, $taskData);
          }
          catch (Exception $e) {
               $this->pushError($e->getMessage());
               return (false);
          }
          
          return ($task);
     }
     
     //Création d'un suivi
     function addTask($formData, $formFiles = array()) {
    
          global $localsite;
    
          $formData['owner']  = $this->user;
          $formData['status'] = 'toplan';
          $formData['initial_date'] = $formData['excepted_date'];
          
          $fields = array(
              'name'          => 'setName',
              //'short_desc'  => 'setShortDesc',
              'long_desc'     => 'setLongDesc',
              'start_date'    => 'setStartDate',
              'excepted_date' => 'setExceptedDate',
              'initial_date'  => 'setInitialDate',
              'category'      => 'setCategory',
              'owner'         => 'setOwner',
              'in_charge'     => 'setInCharge',
              'members'       => 'setMembers',
              'link'          => 'setLink',
              'link_type'     => 'setLinkType',
              'status'        => 'setStatus',
              'priority'      => 'setPriority',
              'type'          => 'setType',
              'duration'      => 'setDuration',
              'duration_real' => 'setDuration_real'
          );
          
          $hasFailed = false;
          
          try {
               $task = new Task($this->db, null);
          }
          catch (Exception $e) {
               $this->pushError($e->getMessage());
               return (false);
          }
          
          foreach ($fields as $field => $fun) {
               try {
                    $task->$fun($formData[$field]);
               }
               catch (Exception $e) {
                    $this->pushError($e->getMessage());
                    $hasFailed = true;
               }
          }

         // TODO: Rewrite properly
         $date = new DateTime('now');
         if ($formData['type'] == 'bug')
           switch ($formData['short_date']) {
           case 0:
             //TODO: use setter.
             $task->exceptedDate = $date->add(new DateInterval('PT1H'));
             break ;
           case 1:
             $task->exceptedDate = $date->add(new DateInterval('PT4H'));
             break ;
           case 2:
             $task->exceptedDate = $date->add(new DateInterval('P1D'));
             break ;
           }
         $task->initialDate = $task->exceptedDate;

         if (!$hasFailed && $taskid = $task->save()) {
           $task->setId($taskid);

           //Insertion des pièces jointes
           if(!empty($formFiles)) $task->save_file($formFiles);

           $tasktype = $formData['type'];
           $this->pushSuccess(ucfirst($tasktype).' ajouté');

           $this->log->setTaskId($taskid);
           $this->log->add('Création du '.$tasktype);
           $this->sendTaskMail($task, $formData['short_date']);

           if(isset($formData['remote'])){
               $send=new send_task($formData['remote']);
               $return=$send->create_task($task,$localsite);
               $taskremote=array();
               foreach($return as $site=>$data)
                   if($data['result']=="OK"){
                     $this->pushSuccess($site." : ".$data['result']);
                     $taskremote[$site]=$data['taskid'];
                   }else
                     $this->pushError($site." : ".$data['error']);

               $task->setRemote($taskremote);
               $task->save();
           }

           return (true);
         }
         return (false);
  }

     function editTask($taskid, $formData){
          
          global $localsite;
  
          $this->log->setTaskId($taskid);
          
          $fields = array(
               'name'          => 'name',
               'short_desc'    => 'shortDesc',
               'long_desc'     => 'longDesc',
               'category'      => 'category',
               'in_charge'     => 'inCharge',
               'members'       => 'members',
               'link'          => 'link',
               'link_type'     => 'linkType',
               'status'        => 'status',
               'duration'      => 'duration',
               'duration_real' => 'duration_real',
               'priority'      => 'priority',
               'type'          => 'type'
          );
          $hasFailed = false;
          
          try {
               
               $task = $this->getTask($taskid);
          } 
          catch (Exception $e) {
               
               $this->pushError($e->getMessage());
               return (false);
          }

          foreach($fields as $field => $fun){
      
               try {

                    if(isset($formData[$field]) && $formData[$field] !==  $task->$fun()) {
                         
                         $before = (is_array($task->$fun()) ? implode(" ",$task->$fun()) : $task->$fun());
                         $after  = (is_array($formData[$field]) ? implode(" ",$formData[$field]) : $formData[$field]);

                         $log .= PHP_EOL.$field.' : '.$before.' -> '.$after;
                         $setter = 'set'.ucfirst($fun); // TODO: maybe define the setter name
                         $task->$setter($formData[$field]);
                    }
               }
               catch (Exception $e){
                    
                    $this->pushError($e->getMessage());
                    $hasFailed = true;
               }
          }
          
          try {
               
               $before = $task->exceptedDate()->format('d/m/y');
               
               $date = new DateTime('now');
      
               if(is_null($formData['short_date'])) $formData['short_date'] = 3;
               
               if(isset($formData['short_date'])) {
                    
                    switch($formData['short_date']){
                         case 0:
                              
                              //todo: use setter.
                              $task->exceptedDate = $date->add(new DateInterval('PT1H'));
                              break;
                         
                         case 1:
                              
                              $task->exceptedDate = $date->add(new DateInterval('PT4H'));
                              break;
                              
                         case 2:
                              
                              $task->exceptedDate = $date->add(new DateInterval('P1D'));
                              break;
                      
                         case 3:
                              
                              if (isset($formData['excepted_date']) && $formData['excepted_date'] != $before) {
                                   $task->setExceptedDate($formData['excepted_date']);
                                   $log .= PHP_EOL.'excepted_date : '.$before.' -> '.$task->exceptedDate()->format('d/m/y`');
                              }
                              break;
                    }
               }
      
               if (!$hasFailed && $task->save()) {
                    
                    $tasktype = 'suivi';
                    $this->pushSuccess(ucfirst($tasktype).' modifié');
                    $this->log->add(ucfirst($tasktype).' modifié :'.$log);

                    $this->sendTaskMail($task,3,$log);
     
                    if(isset($formData['remote'])){
                         
                         $send = new send_task($formData['remote']);
                         $return = $send->create_task($task,$localsite);
                         $taskremote = array();
                         
                         foreach($return as $site=>$data){
                              
                              if($data['result']=="OK"){
                                   
                                   $this->pushSuccess($site." : ".$data['result']);
                                   $taskremote[$site]=$data['taskid'];
                              }else{
                                   $this->pushError($site." : ".$data['error']);
                              }
                         }

                         $task->setRemote($taskremote);
                         $task->save();
                    }
      
                    return (true);
               }
          }
          catch (Exception $e){
               
               $this->pushError($e->getMessage());
          }
          
          return (false);
     }

     function listTasks() {
    
          $tasks = array();
          
          if($this->taskSettings('show_closes') != 'on') $filterClosedTasks = ' AND `status` NOT IN ("closed", "finished")';

          $cat = $this->taskSettings('filter_cat');
          if(array_key_exists($cat, Task::listCategory())){
               
               $filterCategory = ''
               . ' AND `category` LIKE "'.mysqli_real_escape_string($this->db->link_id,$cat).'"';
          }
          
          $status = $this->taskSettings('filter_status');
          if(array_key_exists($status, Task::listStatus())){
               
               $filterStatus = ''
               . ' AND `status` LIKE "'.mysqli_real_escape_string($this->db->link_id,$status).'"';
          }
          
          $types = $this->taskSettings('filter_types');
          if(array_key_exists($types, Task::listTypes())){
               
               $filterTypes = ''
               . ' AND `type` LIKE "'.mysqli_real_escape_string($this->db->link_id,$types).'"';
          }
          
          $priorite = $this->taskSettings('filter_priorite');
          if(array_key_exists($priorite, Task::listPriorites())){
               
               $filterPriorite = ''
               . ' AND p.priorite_id = "'.$priorite.'"';
          }
          
          $resp = $this->taskSettings('filter_resp');
          $interv = $this->taskSettings('filter_interv');
          $owner = $this->taskSettings('filter_own');
          if(Task::dbMembersExists($this->db, array($resp)) && Task::dbMembersExists($this->db, array($interv)) && Task::dbMembersExists($this->db, array($owner))){
              $filterMember = ' AND (in_charge LIKE "'.mysqli_real_escape_string($this->db->link_id,$resp).'"';
              $filterMember .= ' OR `members` LIKE "[%\"'.mysqli_real_escape_string($this->db->link_id,$interv).'\"%]")';
          } else if(Task::dbMembersExists($this->db, array($resp))){
               $filterMember = ' AND in_charge LIKE "'.mysqli_real_escape_string($this->db->link_id,$resp).'"';
          } else if(Task::dbMembersExists($this->db, array($interv))){
               $filterMember .= ' AND `members` LIKE "[%\"'.mysqli_real_escape_string($this->db->link_id,$interv).'\"%]"';
          } else if(Task::dbMembersExists($this->db, array($owner))){
               $filterMember .= ' AND owner LIKE "'.mysqli_real_escape_string($this->db->link_id,$owner).'"';
          }

          if($this->taskSettings('restrict_resp') == 1)
          {
              $filterMember .= ' AND in_charge NOT LIKE "'.mysqli_real_escape_string($this->db->link_id,$owner).'"';
              //$filterMember .= ' AND `members` NOT LIKE "[%\"'.mysqli_real_escape_string($this->db->link_id,$owner).'\"%]"';
          } 

          $tasksearch = strip_tags($this->taskSettings('tasksearch'));
          
          // modif TSI pour recherche rapide taches
          if(!is_null($tasksearch)) {
              $filterSearch = " AND (name LIKE '%".$tasksearch."%' OR short_desc LIKE '%".$tasksearch."%' OR long_desc LIKE '%".$tasksearch."%' ";
              $filterSearch .= "OR id LIKE '".$tasksearch."%')";
          }
          
          $tri_champs2 = '';
          $tri_order2  = '';
          switch($this->taskSettings('tri_type'))
          {
              case 'etat':
              default:
                  $tri_champs  = 'status';
                  $tri_champs2 = ', excepted_date';
                  $tri_order2  = 'ASC';
                  break;

              case 'datefin':
                  $tri_champs  = 'excepted_date';
                  break;

              case 'type':
                  $tri_champs  = 'si.id';
                  break;

              case 'priorite':
                  $tri_champs  = 'si.priorite_id';
                  break;

              case 'intitule':
                  $tri_champs  = 'si.name';
                  break;

              case 'demandeur':
                  $tri_champs  = 'si.owner';
                  break;

              case 'responsable':
                  $tri_champs  = 'si.in_charge';
                  break;

              case 'duree':
                  $tri_champs  = 'si.duration';
                  break;
          }

          switch($this->taskSettings('tri_order'))
          {
              case 'asc':
              default:
                  $tri_order = 'ASC';
                  break;

              case 'desc':
                  $tri_order = 'DESC';
                  break;
          }

          $sql = "SELECT *";
          $sql .= " FROM suivi_interne si";
          $sql .= " LEFT JOIN priorite p ON (p.priorite_id = si.priorite_id)";
          $sql .= " WHERE 1";
          $sql .= $filterClosedTasks;
          $sql .= $filterCategory;
          $sql .= $filterStatus;
          $sql .= $filterTypes;
          $sql .= $filterPriorite;
          $sql .= $filterMember;
          $sql .= $filterSearch; 
          //$sql .= " ORDER BY si.priorite_id"; //Toujours trié par priorité de base
          //$sql .= ',' . $tri_champs . ' ' . $tri_order . $tri_champs2 . ' ' . $tri_order2;
          $sql .= " ORDER BY " . $tri_champs . " " . $tri_order . $tri_champs2 . " " . $tri_order2;
          $query = $this->db->query($sql); 
          
          if(!$query) return false;
         
          while($task = $this->db->fetch_array($query)){
               
               try {
                    array_push($tasks, new Task($this->db, $task));
               }
               catch(Exception $e){
                    $taskId = $this->taskSettings('prefix').'#'.$task['id'];
                    $this->pushError($taskId.': '.$e->getMessage());
                    //return (false);
               }
          }

          return ($tasks);
     }

  function addCat($newCat) {
    try {
      $catList = Task::listCategory();
      $newCat = trim($newCat);
      if (in_array($newCat, $catList))
        throw new Exception(
          'La catégorie "'.htmlentities($newCat,ENT_COMPAT,"ISO-8859-15").'" existe déjà');
      if (strlen($newCat) < 1 || strlen($newCat) > 200)
        throw new Exception(
          'Le nom de la catégorie doit être compris entre 1 et 200 caractères');
      $name = mysqli_real_escape_string($this->db->link_id,$newCat);
      $insertCat = $this->db->query('
        INSERT INTO `suivi_interne_cat`(`name`)
        VALUES("'.$name.'");
      ');
      if (!$insertCat)
        throw new Exception('Requête vers la base de données échouée');
      $this->pushSuccess(
        'La catégorie "'.htmlentities($newCat,ENT_COMPAT,"ISO-8859-15").'" à été créee');
    }
    catch (Exception $e) {
      $this->pushError($e->getMessage());
      return (false);
    }
    return (true);
  }

  function addLog($taskId, $data) {
    $message = trim($data['message']);
//     if (empty($message))
//       return (false);
    $tasktype = 'suivi';
    $msgtype=(isset($data['type'])?$data['type']:"msg");
    $destinataire=(isset($data['destinataire'])?intval($data['destinataire']):0);
    $moyen=(isset($data['moyen'])?$data['moyen']:"");
    $rappel=0;
    if($data['rappel']!=""){
  $d=DateTime::createFromFormat("j#n#Y G*i",$data['rappel']);
  if($d!=false) $rappel=$d->getTimestamp();
    }

    try {
      if (!preg_match('/(\d+)/', $taskId))
  throw new Exception('Numéro de '.$tasktype.' invalide');
      $count = $this->db->query_first('
  SELECT COUNT(`id`)
  FROM `suivi_interne`
  WHERE `id` = '.$taskId
      );
      if ($count && $count[0] == 0)
  throw new Exception('Le '.$tasktype.' n\'existe pas');
      $message = mysqli_real_escape_string($this->db->link_id,stripslashes($message));
      $addLog = $this->db->query('
  INSERT INTO `suivi_interne_log`(`task_id`, `type`, `destinataire`, `moyen`, `author`, `message`, `rappel`, `pour`, `hide`)
  VALUES("'.$taskId.'", "'.$msgtype.'",'.$destinataire.', "'.$moyen.'", "'.$this->user().'", "'.$message.'",'.$rappel.',"'.$data['pour'].'",'.intval($data['hide']).');
      ');
      if (!$addLog)
  throw new Exception('Requête vers la base de données échouée');
    }
    catch (Exception $e) {
      $this->pushError($e->getMessage());
      return (false);
    }
    return (true);
  }

  function sendTaskMail($task, $shortDate=3,$log=null) {
    global $host;
    $emails = $this->emails();
    $members = $task->members();
    $taskMembers = $members;
    $taskMembers[] = $task->inCharge();
    if (!is_array($taskMembers))
      return (true);
    foreach ($taskMembers as $login)
      $sendMembers[$login] = $emails[$login];

    if(!in_array('philippe', $sendMembers)) $sendMembers['philippe'] = 'pr@mon-espace-cuisson.com'; //Demande de Philippe du 06/02/17 de le rajouter dans toutes les créations de suivis
    $owner = (array_key_exists($task->owner(), $members))?
      $members[$task->owner()] : $task->owner();
    $inCharge = (array_key_exists($task->inCharge(), $members))?
      $members[$task->inCharge()] : $task->inCharge();
    if ($task->type() == 'bug') {
      switch ($shortDate) {
      case 0:
        $eDate = 'sous 1h';
        $subject = 'URGENT: Bug à corriger';
        break ;
      case 1:
        $eDate = 'sous 4h';
        $subject = 'URGENT: Bug à corriger';
        break ;
      case 2:
        $eDate = 'sous 1j';
        $subject = 'URGENT: Bug à corriger';
        break ;
      default:
        $eDate = strftime('pour le %A %e %B %Y',
          $task->exceptedDate()->format('U'));
        $subject = 'Bug #'.$task->id().' à corriger '.$eDate;
      }
    }
    else {
      $eDate = strftime('pour le %A %e %B %Y',
        $task->exceptedDate()->format('U'));
      $subject = 'Tâche #'.$task->id().' à terminer '.$eDate;
    }

    if(!is_null($log)) $subject="Mise à jour : ".$subject;

    $mail = new email();
    $mail->copymode="bcc";
    $mail->expname = 'Suivi interne';
    $mail->expediteur = $emails[$task->owner()];
    $mail->to = $sendMembers;
    $mail->subject = $subject;
    $mail->body =
      '<p>'.$owner.' vous envoi ce nouveau '.$task->type().
      ' a faire '.$eDate.' :</p>'.
      '<h3>'.$task->name().'</h3>'.
      '<p>'.$task->shortDesc().'</p>'.
      '<p>'.$task->longDesc().'</p>'.
      '<p>Le responsable est '.$inCharge.'</p>'.
      '<p><a href="'.(defined(BACK_URL) ? BACK_URL : 'https://'.$host).'/admin/task.php?a=showTask&task='.
      $task->id().'&'.$task->type().'">Voir le '.$task->type().'</a></p>';
    if(!is_null($log)) $mail->body.="<p>Mise à jour :".nl2br($log)."</p>";
    return ($mail->sendmail());
  }

  function emails($active_only=true) {
    $active = (($active_only)?'WHERE `actif` = "1"':null);
    $query=$this->db->query("
      SELECT `username`, `mail_contact`
      FROM `parametresgeneraux`
      $active
      ORDER BY `nom_contact`
    ");
    $members = array();
    while($user=$this->db->fetch_array($query))
      $members[$user['username']] = $user['mail_contact'];
    return ($members);
  }

     function members($active_only = true) {

          $active = (($active_only)?' WHERE `actif` = "1"': null);

          $sql = "SELECT `username`, `nom_contact`";
          $sql .= " FROM `parametresgeneraux` ";
          $sql .= $active;
          $sql .= " ORDER BY `nom_contact`";
          $query = $this->db->query($sql);
          $members = array();

          while($user = $this->db->fetch_array($query)) $members[$user['username']] = $user['nom_contact'];

          return ($members);
     }

  function fournisseurs() {
    $query=$this->db->query("
      SELECT `societe`
      FROM `fournisseur`
      GROUP BY `societe`
      ORDER BY `societe`
    ");
    $fournisseurs = array();
    while($f=$this->db->fetch_array($query))
      $fournisseurs[$f['societe']] = $f['societe'];
    return ($fournisseurs);
  }

  function priorities() {
    $query=$this->db->query("
      SELECT `priorite_id`, `priorite_nom`
      FROM `priorite`
      ORDER BY `priorite_id`
    ");
    $priorities = array();
    while($priority=$this->db->fetch_array($query))
      $priorities[$priority['priorite_id']] = $priority['priorite_nom'];
    return ($priorities);
  }
  
  function checkPerm($task, $id, $user=null) {
    if ($user == null)
      $user = $this->user();
    $adm = array('ariel','philippe','benoit','jessica','jbandre');
    if (in_array($user, $adm))
      return (true); // adm have all permissions
    switch ($id) {
      case 'closeTask':
      case 'editTask':
          return ($user == $task->owner());
      break;    
    }
    return (false);
  }
};

?>
