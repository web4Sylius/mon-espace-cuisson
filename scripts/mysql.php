<?php
error_reporting(7);

class DB_Sql {
  var $database = "";

  var $link_id  = 0;
  var $query_id = 0;
  var $record   = array();

  var $errdesc    = "";
  var $errno   = 0;
  var $reporterror = 1;

  var $server   = "localhost";
  var $user     = "root";
  var $password = "";

  function connect() {
    global $usepconnect;
    // connect to db server

    if ( 0 == $this->link_id ) {
      if ($this->password=="") {
        if ($usepconnect==1) {
          $this->link_id=mysql_pconnect($this->server,$this->user);
        } else {
          $this->link_id=mysql_connect($this->server,$this->user);
        }
      } else {
        if ($usepconnect==1) {
          $this->link_id=mysql_pconnect($this->server,$this->user,$this->password);
        } else {
		  $this->link_id=mysql_connect($this->server,$this->user,$this->password);
        }
      }
      if (!$this->link_id) {
        $this->halt("Link-ID == false, connect failed");
      }
      if ($this->database!="") {
        if(!mysql_select_db($this->database, $this->link_id)) {
          $this->halt("cannot use database ".$this->database);
        }
      }
    }
  }

  function geterrdesc() {
    $this->error=mysql_error();
    return $this->error;
  }

  function geterrno() {
    $this->errno=mysql_errno();
    return $this->errno;
  }

  function select_db($database="") {
    // select database
    if ($database!="") {
      $this->database=$database;
    }

    if(!mysql_select_db($this->database, $this->link_id)) {
      $this->halt("NE PEUX PAS UTILISER LA BASE DE DONNEE ".$this->database);
    }

  }

  function query($query_string,$logtable="",$artid=0) {
  	if($logtable!=""){
		mysql_query("INSERT INTO $logtable (id, dateline, datefr, requete) VALUES ('$artid', '".time()."', '".date('Y-m-d')."', '".addslashes($query_string)."');",$this->link_id);
	}
    $this->query_id = mysql_query($query_string,$this->link_id);
    if (!$this->query_id) {
      $this->halt("Invalid SQL: ".$query_string);
    }
    return $this->query_id;
  }

  function fetch_array($query_id=-1,$query_string="") {
    // retrieve row
    if ($query_id!=-1) {
      $this->query_id=$query_id;
    }
    if ( isset($this->query_id) ) {
      $this->record = mysql_fetch_array($this->query_id);
    } else {
      if ( !empty($query_string) ) {
        $this->halt("Invalid query id (".$this->query_id.") on this query: $query_string");
      } else {
        $this->halt("Invalid query id ".$this->query_id." specified");
      }
    }

    return $this->record;
  }


  function fetch_object($query_id=-1,$query_string="") {
    // retrieve row
    if ($query_id!=-1) {
      $this->query_id=$query_id;
    }
    if ( isset($this->query_id) ) {
      $this->record = mysql_fetch_object($this->query_id);
    } else {
      if ( !empty($query_string) ) {
        $this->halt("Invalid query id (".$this->query_id.") on this query: $query_string");
      } else {
        $this->halt("Invalid query id ".$this->query_id." specified");
      }
    }

    return $this->record;
  }



  function free_result($query_id=-1) {
    // retrieve row
    if ($query_id!=-1) {
      $this->query_id=$query_id;
    }
    return mysql_free_result($this->query_id);
  }

  function query_first($query_string) {
    // does a query and returns first row
    $query_id = $this->query($query_string);
    $returnarray=$this->fetch_array($query_id, $query_string);
    $this->free_result($query_id);
    return $returnarray;
  }

  function data_seek($pos,$query_id=-1) {
    // goes to row $pos
    if ($query_id!=-1) {
      $this->query_id=$query_id;
    }
    return mysql_data_seek($this->query_id, $pos);
  }

  function num_rows($query_id=-1) {
    // returns number of rows in query
    if ($query_id!=-1) {
      $this->query_id=$query_id;
    }
    return mysql_num_rows($this->query_id);
  }

  function num_fields($query_id=-1) {
    // returns number of fields in query
    if ($query_id!=-1) {
      $this->query_id=$query_id;
    }
    return mysql_num_fields($this->query_id);
  }

  function insert_id() {
    // returns last auto_increment field number assigned
    return mysql_insert_id($this->link_id);
  }

  function affected_rows() {
    // returns number of affected rows
    return mysql_affected_rows($this->link_id);
  }

  function close() {
  	// closes connection to the database
	return mysql_close();
  }

  function halt($msg) {
    $this->errdesc=mysql_error();
    $this->errno=mysql_errno();
    // prints warning message when there is an error
    global $adminmail , $_SERVER, $host ;


	$f = fopen("/home/hairprod/www/admin/dberror.txt","a");
	fputs($f, "[ ".date("j-m-Y H:i")." ]	[ $this->errdesc ]	[ Script: $GLOBALS[$bburl]" . $_SERVER['REQUEST_URI'] . " ]	[ Referer: " . $_SERVER['HTTP_REFERER'] . " ]\n");
	fclose($f);

//    if ($this->reporterror==1) {
      $message="Erreur de requette SQL :\n\n$msg\n";
      $message.="détail : " . $this->errdesc . "\n\n";
      $message.="numéro : " . $this->errno . "\n\n";
      $message.="Date: ".date("l dS of F Y h:i:s A")."\n";
      $message.="Script: $GLOBALS[boardurl]" . $_SERVER['REQUEST_URI'] . "\n";
      $message.="Referer: ".$_SERVER['HTTP_REFERER']."\n";
//      if ($adminmail) {
//        @mail ($adminmail,"erreur BDD de $GLOBALS[bbtitle]",$message,"From: $adminmail");
//      }
      echo "<html><head><title>$GLOBALS[bbtitle] EREUR BASE DE DONNEES</title><style>P,BODY{FONT-FAMILY:tahoma,arial,sans-serif;FONT-SIZE:11px;}</style><body>\n\n";
      echo "</table></td></tr></table></form>\n<blockquote><p>&nbsp;</p><p><b>Il y a un probleme avec la base de données de $GLOBALS[bbtitle].</b><br>\n";
      echo "Un e-mail a été envoyé au responsable technique qui résoudra ce problème dans les plus brefs délais.</p>";
      echo "<p>Nous sommes désolés du dérangement.</p><pre>$message</pre>";
      echo "</blockquote></body></head></html>";
      exit;
//    }
  }
}
?>