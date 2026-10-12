<?php
ini_set('display_errors',1);

function nice($var, $color = 'black'){
          echo "<div style=\"color:".$color.";\">";
          echo "<p style=\"color:red; font-weight:bold;\">Contenu de la variable :</p>";
          if(is_array($var) || is_object($var)){
                    echo "<pre>";
                    print_r($var);
                    echo "</pre>";   
          } else {
                    var_dump($var);
          }
          echo "</div>";
}

function nicex($var){
          nice($var);
          exit();
}

require_once($_SERVER['DOCUMENT_ROOT']."/class/host.php");
$host_conf = new Host();
$host=$host_conf->host;
require_once($_SERVER['DOCUMENT_ROOT']."/configurations/".$host."/config_variables.php");
require_once($rootpath."configurations/".$host."/config_mysql.php");
require_once($rootpath."class/mysql.php");
require_once($rootpath."class/devises.class.php");
require_once($rootpath."class/template.class.php");
require_once($rootpath."class/fonctions.php");

require_once($rootpath."admin/comparateur/amazon/amazonapi/Samples/.config.inc.php");
require_once($rootpath."admin/comparateur/amazon/amazonapi/Client.php");
require_once($rootpath."admin/comparateur/amazon/amazonapi/Model/SubmitFeedRequest.php");

include($rootpath."admin/comparateur/amazon/amazonapi/Samples/SubmitFeedSample.php");

