<?php
require_once __DIR__.'/../secrets.php';


class wp {

  private $link;
  
  private $DB_NAME='wordpress';
  private $DB_USER=WP_DB_USER;
  private $DB_PASSWORD=WP_DB_PASSWORD;
  private $DB_HOST='localhost';

  private $permalink_structure;
  private $blogurl;
  
  function __construct() {
      $this->link=mysqli_connect($this->DB_HOST,$this->DB_USER,$this->DB_PASSWORD);
      if(!$this->link) return false;

      mysqli_select_db($this->link,$this->DB_NAME);

      $this->permalink_structure=$this->option("permalink_structure");
      $this->blogurl=$this->option("siteurl");
  }

  function posts($nb) {
      $return=array();
      $req=mysqli_query($this->link,"SELECT * FROM `wp_posts` WHERE `post_type` LIKE 'post' AND post_status='publish' ORDER BY post_date DESC LIMIT $nb");
      while($post=mysqli_fetch_array($req))
	  $return[]=array(
	      "title"=>$post['post_title'],
	      "permalink"=>$this->permalink($post)
	  );

      return $return;
  }

  function option($name) {
      $req=mysqli_query($this->link,"SELECT option_value FROM wp_options WHERE option_name='".addslashes($name)."'");
      $opt=mysqli_fetch_array($req);
      return $opt[0];
  }

  function permalink($post) {

      $rewritecode = array(
		'%year%',
		'%monthnum%',
		'%day%',
		'%hour%',
		'%minute%',
		'%second%',
		$leavename? '' : '%postname%',
		'%post_id%',
		'%category%',
		'%author%',
		$leavename? '' : '%pagename%',
	);

	$unixtime = strtotime($post['post_date']);
	$date = explode(" ",date('Y m d H i s', $unixtime));
	$rewritereplace =
	array(
		$date[0],
		$date[1],
		$date[2],
		$date[3],
		$date[4],
		$date[5],
		$post['post_name'],
		$post['Id'],
		$category,
		$author,
		$post['post_name'],
	);

	return $this->blogurl.str_replace($rewritecode, $rewritereplace, $this->permalink_structure);
  }
}