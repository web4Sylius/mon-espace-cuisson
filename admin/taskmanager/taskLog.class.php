<?php

class TaskLog {
  private $id;
  private $taskId;
  private $type;
  private $date;
  private $author;
  private $msg;
  private $msgtypes=array("log","msg","client","pro");

  function __construct($db, $taskId, $author) {
    $this->db     = $db;
    $this->taskId = $taskId;
    $this->author = $author;
  }

  function setTaskId($id) {
    $this->taskId = $id;
  }
  function setAuthor($author) {
    $this->author = $author;
  }

  function add($msg, $type='log') {
    if (!in_array($type, $this->msgtypes))
      throw new Exception("Type invalide");
    $author  = mysqli_real_escape_string($this->db->link_id,stripslashes($this->author));
	$message = mysqli_real_escape_string($this->db->link_id,stripslashes($msg));
    $this->db->query('
      INSERT INTO `suivi_interne_log`(`task_id`, `type`, `author`, `message`)
      VALUES("'.$this->taskId.'", "'.$type.'", "'.$author.'", "'.$message.'");
    ');
  }

  function msglist() {
    $query = $this->db->query('
      SELECT *
      FROM `suivi_interne_log`
      WHERE `task_id` = "'.$this->taskId.'"
      ORDER BY `date` DESC;'
    );
    $i = 0;
    while ($data = $this->db->fetch_array($query)) {
      $msg[strtotime($data['date'])] = $data;
      $i++;
    }

    return ($msg);
  }

  function emaillist(){
      $msg=array();
      $query=$this->db->query("SELECT * FROM mail_outbox WHERE suivi_interne_id='".$this->taskId."' ORDER BY date DESC");
      while($data=$this->db->fetch_array($query)){
	  $msg[$data['date']]=$data;
      }
      return $msg;
  }
};

?>