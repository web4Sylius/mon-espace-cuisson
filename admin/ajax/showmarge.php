<?php
include "../marge.php";
require_once($_SERVER['DOCUMENT_ROOT']."/admin/admin_global.php");
require_once(SITE_PATH.'/class/DAOgarantie.class.php');
require_once(SITE_PATH.'/class/DAOarticle.class.php');
require_once(SITE_PATH.'/class/garantie.class.php');
require_once(SITE_PATH.'/class/DAOlignefacturegarantie.class.php');
require_once(SITE_PATH.'/class/stock.class.php');
require_once(SITE_PATH.'/class/frais_port.php');
require_once(SITE_PATH.'/class/fournisseur.class.php');
if($_GET['factid'] != null) {
$margetotal=new marge($DB_site2,$_GET['factid']);
		$margett = $margetotal->margetotal($DB_site2,$_GET['factid']);

		echo $margett['margett_pc'];
	}