<?php

class Task {
  private $db;
  public $id;
  private $name;
  private $shortDesc;
  private $longDesc;
  private $startDate;
  
  // TODO: rewrite setters and make attributes private!
  public  $initialDate;
  public  $excepteddate;
  
  private $endDate;
  private $duration;
  private $duration_real;
  private $category;
  private $owner;
  private $inCharge;
  private $members=array();
  private $link;
  private $linkType;
  private $status;
  private $remote;
  public $type;

  function __construct($db, $data) {
    if (!is_a($db, 'DB_Sql'))
      throw new Exception('DB_Sql attendu');
    $this->db = $db;
    if ($data)
      $this->load($data);
  }

  // Getters
  function id() {
    return ($this->id);
  }
  function name() {
    return ($this->name);
  }
  function shortDesc() {
    return ($this->shortDesc);
  }
  function longDesc() {
    return ($this->longDesc);
  }
  function startDate() {
    return ($this->startDate);
  }
  function initialDate() {
    return ($this->initialDate);
  }
  function exceptedDate() {
    return ($this->exceptedDate);
  }
  function endDate() {
    return ($this->endDate);
  }
  function duration() {
    return $this->duration;
  }
  function duration_real() {
    return $this->duration_real;
  }
  function category() {
    return ($this->category);
  }
  function owner() {
    return ($this->owner);
  }
  function inCharge() {
    return ($this->inCharge);
  }
  function members() {
    return ($this->members);
  }
  function link() {
    return ($this->link);
  }
  function linkType() {
    return ($this->linkType);
  }
  function status() {
    return ($this->status);
  }
  function type() {
    return ($this->type);
  }
  function remote() {
    return $this->remote;
  }
  function priority() {
    return $this->priority;
  }
  function priority_name() {
    return $this->priority_name;
  }

  // Setters
  function setId($id) {
    if (!is_numeric($id))
      throw new Exception("L'id doit être un entier");
    $this->id = $id;
  }
  function setName($name) {
    if (empty($name))
      throw new Exception("Le nom ne peut être vide");
    if (strlen($name) > 200)
      throw new Exception("Le nom ne peut dépasser 200 caractères");
    $this->name = $name;
  }
  function setShortDesc($shortDesc) {
    if (empty($shortDesc))
      throw new Exception("La description courte ne peut être vide");
    if (strlen($shortDesc) > 200)
      throw new Exception("La description courte ne peut dépasser 500 caractères");
    $this->shortDesc = $shortDesc;
  }
  function setLongDesc($longDesc) {
    if (empty($longDesc))
      throw new Exception("La description longue ne peut être vide");
    $this->longDesc = $longDesc;
  }
  function setStartDate($startDate) {
    if (empty($startDate))
      throw new Exception("La date de début ne peut être vide");
    if (!preg_match('#(\d{1,2})/(\d{1,2})/(\d{4})#', $startDate, $date))
      throw new Exception("Le format de la date de début doit être de la forme jj/mm/aaaa");
    if (!checkdate($date[2],$date[1],$date[3]))
      throw new Exception("La date de début n'est pas valide");
    $this->startDate = DateTime::createFromFormat(
      'Y-m-d',
      "$date[3]-$date[2]-$date[1]"
    );
  }
  function setInitialDate($initialDate) {
    if (empty($initialDate))
      throw new Exception("La date initiale ne peut être vide");
    if (!preg_match('#(\d{1,2})/(\d{1,2})/(\d{4})#', $initialDate, $date))
      throw new Exception("Le format de la date initiale doit être de la forme jj/mm/aaaa");
    if (!checkdate($date[2],$date[1],$date[3]))
      throw new Exception("La date initiale n'est pas valide");
    $this->initialDate = DateTime::createFromFormat(
      'Y-m-d',
      "$date[3]-$date[2]-$date[1]"
    );
  }
  function setExceptedDate($exceptedDate) {
    if (empty($exceptedDate))
      throw new Exception("La date prévue ne peut être vide");
    if (!preg_match('#(\d{1,2})/(\d{1,2})/(\d{4})#', $exceptedDate, $date))
      throw new Exception("Le format de la date prévue doit être de la forme jj/mm/aaaa");
    if (!checkdate($date[2],$date[1],$date[3]))
      throw new Exception("La date prévue n'est pas valide");
    $this->exceptedDate = DateTime::createFromFormat(
      'Y-m-d',
      "$date[3]-$date[2]-$date[1]"
    );
  }
  function setEndDate($endDate) {
    if (empty($endDate))
      throw new Exception("La date de fin ne peut être vide");
    if (!preg_match('#(\d{1,2})/(\d{1,2})/(\d{4})#', $endDate, $date))
      throw new Exception("Le format de la date de fin doit être de la forme jj/mm/aaaa");
    if (!checkdate($date[2],$date[1],$date[3]))
      throw new Exception("La date de fin n'est pas valide");
    $this->endDate = DateTime::createFromFormat(
      'Y-m-d',
      "$date[3]-$date[2]-$date[1]"
    );
  }
  function setCategory($category) {
    $categoryList = $this->listCategory();
    if (!array_key_exists($category, $categoryList))
      throw new Exception("La catégorie n'existe pas");
    $this->category = $category;
  }
  function setOwner($owner) {
    if (!$this->membersExists(array($owner)))
      throw new Exception("Le créateur n'existe pas");
    $this->owner = $owner;
  }
  function setInCharge($inCharge) {
    if (!$this->membersExists(array($inCharge)))
      throw new Exception("Le responsable n'existe pas");
    $this->inCharge = $inCharge;
  }
  function setMembers($members) {
    if($members[0]=="") unset($members);
    //if (!$this->membersExists($members))
      //throw new Exception("Les intervenants n'existent pas tous");
      
    if(is_null($members)) $members=array();
    $this->members = $members;
  }
  function setLink($link) {
    $this->link = $link;
  }
  function setLinkType($linkType) {
    $this->linkType = $linkType;
  }
  function setStatus($status) {
    $statusList = $this->listStatus();
    if (!array_key_exists($status, $statusList))
      throw new Exception("L'état n'est pas valide");
    $this->status = $status;

    if ($status == 'closed')
      $this->setEndDate(date('d/m/Y'));
  }
  function setPriority($priority) {
    $this->priority = $priority;
  }
  function setPriority_name($priority_name) {
    $this->priority_name = $priority_name;
  }
  function setType($type) {
    if (!in_array($type, array('suivi', 'bug')))
      throw new Exception("Type de tâche inconnu");
    $this->type = $type;
  }
  function setDuration($duration) {
    $this->duration=$duration;
  }
  function setDuration_real($duration_real) {
    $this->duration_real=$duration_real;
  }
  function setRemote($remote) {
    $this->remote=$remote;
  }

  // Methods
  function load($data) {
    try {
      $this->setId($data['id']);
      $this->setName($data['name']);
      //$this->setShortDesc($data['short_desc']);
      $this->setLongDesc($data['long_desc']);
      $this->setStartDate(
	    DateTime::createFromFormat(
          'Y-m-d H:i:s', $data['start_date'])->format('d/m/Y'));
      $this->setInitialDate(
	    DateTime::createFromFormat(
          'Y-m-d H:i:s', $data['initial_date'])->format('d/m/Y'));
      $this->setExceptedDate(
	    DateTime::createFromFormat(
          'Y-m-d H:i:s', $data['excepted_date'])->format('d/m/Y'));
      $this->duration=$data['duration'];
      $this->duration_real=$data['duration_real'];
      $this->setCategory($data['category']);
      $this->setOwner($data['owner']);
      $this->setInCharge($data['in_charge']);
      $this->setMembers(json_decode($data['members']));
      $this->setLink($data['link']);
      $this->setLinkType($data['link_type']);
      $this->setStatus($data['status']);
      $this->setPriority($data['priorite_id']);
      $this->setPriority_name($data['priorite_nom']);
      $this->setType($data['type']);
      $this->setRemote(json_decode($data['remote']));
    }
    catch (Exception $e) {
      throw $e;
      return (false);
    }
    return (true);
  }

     function save() {

          // Protect fields (stripslashes() first because of magic_quotes_gpc)
          $name          = mysqli_real_escape_string($this->db->link_id,stripslashes($this->name));
          $shortDesc     = mysqli_real_escape_string($this->db->link_id,stripslashes($this->shortDesc));
          $longDesc      = mysqli_real_escape_string($this->db->link_id,stripslashes($this->longDesc));
          $category      = mysqli_real_escape_string($this->db->link_id,stripslashes($this->category));
          $owner         = mysqli_real_escape_string($this->db->link_id,stripslashes($this->owner));
          $inCharge      = mysqli_real_escape_string($this->db->link_id,stripslashes($this->inCharge));
          $link          = mysqli_real_escape_string($this->db->link_id,stripslashes($this->link));
          $linkType      = mysqli_real_escape_string($this->db->link_id,stripslashes($this->linkType));
          $status        = mysqli_real_escape_string($this->db->link_id,stripslashes($this->status));
          $priority      = mysqli_real_escape_string($this->db->link_id,stripslashes($this->priority));
          $type          = $this->type;
          $members       = json_encode($this->members);
          $remote        = json_encode($this->remote);
          $duration      = $this->duration;
          $duration_real = $this->duration_real;

          $dates = array('startDate', 'initialDate', 'exceptedDate', 'endDate');
          
          foreach ($dates as $date) {
               
               if(is_a($this->$date(), 'DateTime')){
                    $$date = $this->$date()->format('Y-m-d H:i:s');
               } else {
                    $$date = null;
               }
          }

          // Build query
          $id = $this->id;
          $queryType = ((!$id)? "INSERT INTO" : "UPDATE");
          $query = $queryType
               . " `suivi_interne` SET "
               . " `name`		= '$name',"
               //. " `short_desc`	= '$shortDesc',"
               . " `long_desc`	= '$longDesc',"
               . " `start_date`	= '$startDate',"
               . " `initial_date`	= '$initialDate',"
               . " `excepted_date`	= '$exceptedDate',"
               . " `end_date`	= '$endDate',"
               . " `duration`	= '$duration',"
               . " `duration_real` = '$duration_real',"
               . " `category`	= '$category',"
               . " `owner`		= '$owner',"
               . " `in_charge`	= '$inCharge',"
               . " `members`		= '$members',"
               . " `link`		= '$link',"
               . " `link_type`	= '$linkType',"
               . " `status`		= '$status',"
               . " `priorite_id`	= '$priority',"
               . " `type`		= '$type',"
               . " `remote`		= '$remote'";
          
          if($id) $query .= " WHERE `id` = '$id'";

          if(!$this->db->query($query)) return (false);
            
          return (($id)? $id : mysqli_insert_id($this->db->link_id));
  }

    function save_file($formFiles){

        $file_name = $formFiles['task_file']['name'];
        $file_size = $formFiles['task_file']['size'];
        $file_ext  = substr($formFiles['task_file']['name'], strpos($formFiles['task_file']['name'], '.') + 1, strlen($formFiles['task_file']['name']));

        $sql = "INSERT INTO suivi_interne_files (";
        $sql .= "taskid";
        $sql .= ", parent";
        $sql .= ", date";
        $sql .= ", utilisateur";
        $sql .= ", name";
        $sql .= ", size";
        $sql .= ", ext";
        $sql .= ", state";
        $sql .= ") VALUES (";
        $sql .= $this->id;
        $sql .= ", 0";
        $sql .= ", NOW()";
        $sql .= ", '".$_COOKIE['admin_username']."'";
        $sql .= ", '".mysqli_real_escape_string($this->db->link_id,$file_name)."'";
        $sql .= ", '".intval($file_size)."'";
        $sql .= ", '".mysqli_real_escape_string($this->db->link_id,$file_ext)."'";
        $sql .= ", 1";
        $sql .= ")";
        $this->db->query($sql);

        $file_id      = $this->db->insert_id();
        $file_newname = $file_id.'.'.$file_ext;

        move_uploaded_file($formFiles['task_file']['tmp_name'], $_SERVER['DOCUMENT_ROOT'].'/admin/taskmanager/fichiers/'.$file_newname);
    }

     static function listStatus() {
          return array(
               'toplan'   => 'A planifier',
               'planned'  => 'Planifié',
               'working'  => 'En cours',
               'waiting'  => 'En attente',
               'tocheck'  => '🔍 A vérifier',
               'closed'   => 'Clôturé'
          );
     }
     
     static function listTypes() {
          return array(
               'suivi'  => 'Tâche',
               'bug'    => 'Bug'
          );
     
     }
     
     static function listPriorites() {
          
          global $DB_site2;
          
          $sql = "SELECT priorite_id, priorite_nom";
          $sql .= " FROM priorite";
          $req = $DB_site2->query($sql);
          while($row = $DB_site2->fetch_array($req)){
               $tabReturn[$row['priorite_id']] = $row['priorite_nom'];
          }
          
          return $tabReturn;
     }
     
     static function listCategory() {
          
          global $DB_site2; //TODO: do better

          $sql = "SELECT `id`, `name`";
          $sql .= " FROM `suivi_interne_cat`";
          $query = $DB_site2->query($sql);
          while ($r = $DB_site2->fetch_array($query)) $cat[$r['id']] = $r['name'];
          
          return ($cat);

          // old static array
          /*return array(
               'gen'	=> 'Général',
               'art'	=> 'Articles',
               'anim'	=> 'Animation',
               'comm'	=> 'Commercial',
               'qual'	=> 'Quailté SAV',
               'fourn'	=> 'Fournisseur',
               'compta'	=> 'Comptabilité'
          );*/
     }

  private function membersExists($usernames) {
    return ($this->dbMembersExists($this->db, $usernames));
  }

  static function dbMembersExists($db, $usernames) {
    if (empty($usernames))
      return (true);
    if (!is_array($usernames))
      return (false);
    $query = '
      SELECT COUNT(`username`)
      FROM `parametresgeneraux` WHERE `username` IN(';
    foreach ($usernames as $username)
      $query .= '"'.mysqli_real_escape_string($db->link_id,$username).'",';
    $query = substr($query, 0, -1); // Remove last comma
    $query .= ");";
    $result = $db->query_first($query);
    return ($result[0] == count($usernames));
  }
};

?>