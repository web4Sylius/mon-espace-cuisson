<?php
require_once(__DIR__.'/task.class.php');

class TaskReminder {
  private $db;
  private $user;

  function __construct($db, $user) {
    if (!is_a($db, 'DB_Sql'))
      throw new Exception('DB_Sql attendu');
    $this->db = $db;
    $this->user = $user;
  }

  function taskList($today) {
    $user = mysqli_real_escape_string($this->db->link_id,$this->user);
    $today_op = (($today)?'>=':'<');
    $query = $this->db->query('
      SELECT *
      FROM `suivi_interne`
      WHERE `type` = "suivi"
      AND `status` NOT IN ("closed","finished")
      AND (
        `owner` = "'.$user.'"
        OR `in_charge` = "'.$user.'"
        OR `members` LIKE "[%\"'.$user.'\"%]"
      )
      AND `excepted_date` '.$today_op.' "'.date("Y-m-d").'"
      ORDER BY `excepted_date` ASC
    ');
    if (!$query)
	  return (false);
    $tasks = array();
    while($task = $this->db->fetch_array($query))
      try {
        $tasks[$task['id']] = new Task($this->db, $task);
      }
      catch (Exception $e) {
        continue ;
      }
    return ($tasks);
  }

  function show($title, $tasks) {
    if (empty($tasks))
      return (NULL);
    echo <<<HTML
      <div class="rappelsuivi">
        <h1>$title</h1>
        <ul>
HTML;
    foreach ($tasks as $task)
      echo '
        <li>
          <a href="task.php?a=showTask&amp;task='.$task->id().'">
            <strong>['.$task->exceptedDate()->format('d/m/Y').']</strong>
            SI#'.$task->id().' : '.$task->name().'
          </a>
        </li>';
    echo <<<HTML
        </ul>
      </div>
HTML;
  }
};

?>
