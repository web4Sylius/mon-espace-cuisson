<?php
/***
 * Fnac Api Client homepage
 */

  require_once __DIR__.'/autoload.php';

  use FnacApiClient\Client\SimpleClient;
  
  use FnacApiGui\Controller\HomeController;
  use FnacApiGui\Model\HomeModel;
  use FnacApiGui\View\HomeView;

  $client = new SimpleClient();
  $client->init(__DIR__.'/config/config.yml');
  
  $m = new HomeModel();
  $c = new HomeController($m, $client);
  $v = new HomeView($c, $m);
  
  echo $v->output($client);

?>